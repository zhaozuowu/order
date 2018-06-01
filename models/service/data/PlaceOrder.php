<?php
/**
 * @name PlaceOrder.php
 * @desc PlaceOrder.php
 * @author yu.jin03@ele.me
 */

class Service_Data_PlaceOrder
{
    /**
     * @var Dao_Wrpc_Stock
     */
    protected $objDaoWprcStock;

    /**
     * @var Dao_Wrpc_Warehouse
     */
    protected $objDaoWrpcWarehouse;

    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoRalSku;

    /**
     * Service_Data_PlaceOrder constructor.
     */
    public function __construct()
    {
        $this->objDaoWprcStock = new Dao_Wrpc_Stock(Order_Define_Wrpc::NWMS_STOCK_CONTROL_SERVICE_NAME);
        $this->objDaoWrpcWarehouse = new Dao_Wrpc_Warehouse();
    }

    /**
     * 创建上架单
     * @param $arrStockinOrderIds
     * @return array
     * @throws Wm_Error
     */
    public function createPlaceOrder($arrStockinOrderIds)
    {
        //根据入库单号获取入库单详情信息
        $arrStockinOrderInfo = $this->getStockinInfoByStockinOrderIds($arrStockinOrderIds);
        Bd_Log::trace(sprintf("method[%s] stockin_order_ids[%s] stockin_order_infos[%s]",
                        __METHOD__, json_encode($arrStockinOrderIds), json_encode($arrStockinOrderInfo)));
        if (empty($arrStockinOrderInfo)) {
            return [];
        }
        //校验是否已生成上架单
        $arrStockinPlaceOrderInfo = Model_Orm_StockinPlaceOrder::getPlaceOrdersByStockinOrderIds($arrStockinOrderIds);
        Bd_Log::trace(sprintf("method[%s] stockin_order_ids[%s] stockin_place_orders[%s]",
                            __METHOD__, json_encode($arrStockinOrderIds), json_encode($arrStockinOrderInfo)));
        if (!empty($arrStockinPlaceOrderInfo)) {
            return [];
        }
        //创建上架单
        $arrSplitOrderInfo = $this->splitStockinOrderByQuality($arrStockinOrderInfo);
        Bd_Log::trace(sprintf("method[%s] split_order_infos[%s]", __METHOD__, json_encode($arrSplitOrderInfo)));
        if (empty($arrSplitOrderInfo) || (empty($arrSplitOrderInfo['good_skus'])
            && empty($arrSplitOrderInfo['bad_skus']))) {
            return [];
        }
        list($arrOrderList, $arrSkuList, $arrMapOrderList) =
            $this->getCreateParams($arrSplitOrderInfo);
        Bd_Log::trace(sprintf("method[%s] order_list[%s] sku_list[%s] map_order_list",
                        json_encode($arrOrderList), json_encode($arrSkuList), json_encode($arrMapOrderList)));
        //创建上架单
        Model_Orm_PlaceOrder::getConnection()->transaction(function ()
        use ($arrOrderList, $arrSkuList, $arrMapOrderList, $arrStockinOrderIds) {
            Model_Orm_PlaceOrder::batchInsert($arrOrderList);
            Model_Orm_PlaceOrderSku::batchInsert($arrSkuList);
            Model_Orm_StockinPlaceOrder::batchInsert($arrMapOrderList);
            $boolFlag = Model_Orm_StockinOrder::placeStockinOrder($arrStockinOrderIds);
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_CREATE_FAILED);
            }
            $this->autoPlaceOrder($arrOrderList, $arrSkuList);
        });
    }

    /**
     * 自动上架单自动上级
     * @param $arrOrderList
     * @param $arrSkuList
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    protected function autoPlaceOrder($arrOrderList, $arrSkuList) {
        if (empty($arrOrderList)) {
            return [];
        }
        $arrMapOrderSkus = Order_Util_Util::arrayToKeyValues($arrSkuList, 'place_order_id');
        foreach ((array)$arrOrderList as $arrOrderItem) {
            $intIsAuto = $arrOrderItem['is_auto'];
            if ($intIsAuto != Order_Define_PlaceOrder::PLACE_ORDER_IS_AUTO) {
                continue;
            }
            $intPlaceOrderId = $arrOrderItem['place_order_id'];
            if (empty($intPlaceOrderId)) {
                continue;
            }
            $arrSkus = [];
            $arrOrderSkus = $arrMapOrderSkus[$intPlaceOrderId];
            foreach ((array)$arrOrderSkus as $arrOrderSkuItem) {
                $arrSkuItem['sku_id'] = $arrOrderSkuItem['sku_id'];
                $arrSkuItem['place_amount'] = $arrOrderSkuItem['plan_amount'];
                $arrSkuItem['expire_date'] = $arrOrderSkuItem['expire_date'];
                $arrSkuItem['location_code'] = Nscm_Define_Warehouse::VIRTURE_LOCATION_CODE_DEFAULT_STORE;
                $arrSkuItem['area_code'] = Nscm_Define_Warehouse::VIRTURE_AREA_CODE_DEFAULT_STORE;
                $arrSkuItem['roadway_code'] = Nscm_Define_Warehouse::VIRTURE_ROADWAY_CODE_DEFAULT_STORE;
                $arrSkus[] = $arrSkuItem;
            }
            $this->confirmPlaceOrder($intPlaceOrderId, $arrSkus, '', 0);
        }
    }

    /**
     * 批量获取入库单信息
     * @param $arrStockinOrderIds
     * @return array
     */
    protected function getStockinInfoByStockinOrderIds($arrStockinOrderIds)
    {
        $arrStockinInfo = [];
        if (empty($arrStockinOrderIds)) {
            return $arrStockinInfo;
        }
        $arrStockinInfo['stockin_order_ids'] = $arrStockinOrderIds;
        $arrStockinInfoDb = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($arrStockinOrderIds[0]);
        if (empty($arrStockinInfoDb)) {
            return [];
        }
        if (1 == count($arrStockinOrderIds)) {
            $arrStockinSourceInfo = empty($arrStockinInfoDb['source_info']) ?
                [] : json_decode($arrStockinInfoDb['source_info'], true);
            $arrStockinInfo['vendor_id'] = intval($arrStockinSourceInfo['vendor_id']);
            $arrStockinInfo['vendor_name'] = strval($arrStockinSourceInfo['vendor_name']);
            $arrStockinInfo['warehouse_id'] = $arrStockinInfoDb['warehouse_id'];
            $arrStockinInfo['warehouse_name'] = $arrStockinInfoDb['warehouse_name'];
            $arrStockinInfo['stockin_order_type'] = $arrStockinInfoDb['stockin_order_type'];
        } else {
            $arrStockinInfo['warehouse_id'] = $arrStockinInfoDb['warehouse_id'];
            $arrStockinInfo['warehouse_name'] = $arrStockinInfoDb['warehouse_name'];
            $arrStockinInfo['stockin_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
        }
        $arrStockinInfo['skus'] = Model_Orm_StockinOrderSku::getStockinOrderSkusByStockinOrderIds($arrStockinOrderIds);
        return $arrStockinInfo;
    }

    /**
     * 按照良品和非良品拆分上架单
     * @param $arrInput
     * @return array
     */
    protected function splitStockinOrderByQuality($arrInput)
    {
        if (empty($arrInput) || empty($arrInput['skus'])) {
            return [];
        }
        $arrRetOrderInfo = [];
        $arrRetOrderInfo['stockin_order_ids'] = $arrInput['stockin_order_ids'];
        $arrRetOrderInfo['vendor_id'] = $arrInput['vendor_id'];
        $arrRetOrderInfo['vendor_name'] = $arrInput['vendor_name'];
        $arrRetOrderInfo['stockin_order_type'] = $arrInput['stockin_order_type'];
        $arrRetOrderInfo['warehouse_id'] = $arrInput['warehouse_id'];
        $arrRetOrderInfo['warehouse_name'] = $arrInput['warehouse_name'];
        foreach ((array)$arrInput['skus'] as $arrSkuItem) {
            $arrPlaceOrderSkuInfo = [];
            $arrPlaceOrderSkuInfo['sku_id'] = $arrSkuItem['sku_id'];
            $arrPlaceOrderSkuInfo['sku_name'] = $arrSkuItem['sku_name'];
            $arrPlaceOrderSkuInfo['upc_id'] = $arrSkuItem['upc_id'];
            $arrPlaceOrderSkuInfo['upc_unit'] = $arrSkuItem['upc_unit'];
            $arrPlaceOrderSkuInfo['upc_unit_num'] = $arrSkuItem['upc_unit_num'];
            $arrPlaceOrderSkuInfo['sku_net'] = $arrSkuItem['sku_net'];
            $arrPlaceOrderSkuInfo['sku_net_unit'] = $arrSkuItem['sku_net_unit'];
            $arrSkuItem['stockin_order_sku_extra_info'] =
                json_decode($arrSkuItem['stockin_order_sku_extra_info'], true);
            foreach ((array)$arrSkuItem['stockin_order_sku_extra_info'] as $arrExtraInfoItem) {
                if (isset($arrExtraInfoItem['sku_good_amount'])
                    && $arrExtraInfoItem['sku_good_amount'] > 0) {
                    $arrPlaceOrderSkuInfo['plan_amount'] =
                        $arrExtraInfoItem['sku_good_amount'];
                    $arrPlaceOrderSkuInfo['expire_date'] = $arrExtraInfoItem['expire_date'];
                    $arrRetOrderInfo['good_skus'][] = $arrPlaceOrderSkuInfo;
                }
                if (isset($arrExtraInfoItem['sku_defective_amount'])
                    && $arrExtraInfoItem['sku_defective_amount'] > 0) {
                    $arrPlaceOrderSkuInfo['plan_amount'] =
                        $arrExtraInfoItem['sku_defective_amount'];
                    $arrPlaceOrderSkuInfo['expire_date'] = $arrExtraInfoItem['expire_date'];
                    $arrRetOrderInfo['bad_skus'][] = $arrPlaceOrderSkuInfo;
                }
                if (!isset($arrExtraInfoItem['sku_good_amount'])
                    && !isset($arrExtraInfoItem['sku_defective_amount'])) {
                    $arrPlaceOrderSkuInfo['plan_amount'] = intval($arrExtraInfoItem['amount']);
                    $arrPlaceOrderSkuInfo['expire_date'] = intval($arrExtraInfoItem['expire_date']);
                    $arrRetOrderInfo['good_skus'][] = $arrPlaceOrderSkuInfo;
                }
            }
        }
        return $arrRetOrderInfo;
    }

    /**
     * 获取数据创建参数
     * @param $arrInput
     * @return array
     * @throws Wm_Error
     */
    protected function getCreateParams($arrInput)
    {
        $arrOrderList = [];
        $arrSkuList = [];
        $arrMapOrderList = [];
        $arrOrderInfo = [];
        $arrOrderInfo['vendor_id'] = intval($arrInput['vendor_id']);
        $arrOrderInfo['vendor_name'] = strval($arrInput['vendor_name']);
        $arrOrderInfo['warehouse_id'] = intval($arrInput['warehouse_id']);
        $arrOrderInfo['warehouse_name'] = strval($arrInput['warehouse_name']);
        $arrOrderInfo['stockin_order_type'] = intval($arrInput['stockin_order_type']);
        $arrOrderInfo['place_order_status'] = Order_Define_PlaceOrder::STATUS_WILL_PLACE;
        $arrOrderInfo['is_auto'] = Order_Define_PlaceOrder::PLACE_ORDER_NOT_AUTO;
        $intLocationTag = $this->getWarehouseLocationTag($arrInput['warehouse_id']);
        Bd_Log::trace(sprintf("method[%s] location_tag[%s]", __METHOD__, strval($intLocationTag)));
        if (Order_Define_Warehouse::STORAGE_LOCATION_TAG_DISABLE
            == $intLocationTag) {
            $arrOrderInfo['place_order_status'] = Order_Define_PlaceOrder::STATUS_PLACED;
            $arrOrderInfo['is_auto'] = Order_Define_PlaceOrder::PLACE_ORDER_IS_AUTO;
        }
        //非良品订单信息
        if (!empty($arrInput['bad_skus'])) {
            $arrBadSkuOrderInfo['place_order_id'] = Order_Util_Util::generatePlaceOrderId();
            $arrBadSkuOrderInfo['is_defective'] = Nscm_Define_Stock::QUALITY_DEFECTIVE;
            $arrBadSkuOrderInfo = array_merge($arrBadSkuOrderInfo, $arrOrderInfo);
            $arrOrderList[] = $arrBadSkuOrderInfo;
            foreach ((array)$arrInput['bad_skus'] as $intKey => $arrVal) {
                $arrInput['bad_skus'][$intKey]['place_order_id'] =
                    intval($arrBadSkuOrderInfo['place_order_id']);
                $arrInput['bad_skus'][$intKey]['actual_info'] = '';
            }
            $arrSkuList = array_merge($arrSkuList, $arrInput['bad_skus']);
            $arrBadMapOrderList = $this->getMapOrderList($arrInput['stockin_order_ids'], $arrBadSkuOrderInfo['place_order_id']);
            $arrMapOrderList = array_merge($arrMapOrderList, $arrBadMapOrderList);
        }
        //良品订单信息
        if (!empty($arrInput['good_skus'])) {
            $arrGoodSkuOrderInfo['place_order_id'] = Order_Util_Util::generatePlaceOrderId();
            $arrGoodSkuOrderInfo['is_defective'] = Nscm_Define_Stock::QUALITY_GOOD;
            $arrGoodSkuOrderInfo = array_merge($arrGoodSkuOrderInfo, $arrOrderInfo);
            $arrOrderList[] = $arrGoodSkuOrderInfo;
            foreach ((array)$arrInput['good_skus'] as $intKey => $arrVal) {
                $arrInput['good_skus'][$intKey]['place_order_id'] =
                    intval($arrGoodSkuOrderInfo['place_order_id']);
                $arrInput['good_skus'][$intKey]['actual_info'] = '';
            }
            $arrSkuList = array_merge($arrSkuList, $arrInput['good_skus']);
            $arrGoodMapOrderList = $this->getMapOrderList($arrInput['stockin_order_ids'], $arrGoodSkuOrderInfo['place_order_id']);
            $arrMapOrderList = array_merge($arrMapOrderList, $arrGoodMapOrderList);
        }
        return [$arrOrderList, $arrSkuList, $arrMapOrderList];
    }

    /**
     * 计算到效期
     * @param $arrDbSku
     * @return array
     */
    protected function calculateExpire($arrDbSku)
    {
        $arrStockinOrderSkuExtraInfo = json_decode($arrDbSku['stockin_order_sku_extra_info'], true);
        $arrBatchInfo = [];
        foreach ($arrStockinOrderSkuExtraInfo as $skuRow) {
            if ($skuRow['amount'] > 0) {
                if (Order_Define_Sku::SKU_EFFECT_TYPE_PRODUCT == $arrDbSku['sku_effect_type']) {
                    $intProductionTime = intval($skuRow['expire_date']);
                    $intExpireTime = $intProductionTime + intval($arrDbSku['sku_effect_day']) * 86400 - 1;
                    $arrBatchInfo[] = [
                        'expire_time' => $intExpireTime,
                        'production_time' => $intProductionTime,
                        'amount'      => $skuRow['amount'],
                    ];
                } else {
                    $intExpireTime = intval($skuRow['expire_date']) + 86399;
                    $arrBatchInfo[] = [
                        'expire_time' => $intExpireTime,
                        'amount'      => $skuRow['amount'],
                    ];
                }
            }
        }
        return $arrBatchInfo;
    }

    /**
     * 获取关联表写入参数
     * @param $arrStockinOrderIds
     * @param $intPlaceOrderId
     * @return array
     */
    protected function getMapOrderList($arrStockinOrderIds, $intPlaceOrderId)
    {
        $arrMapOrderList = [];
        if (empty($arrStockinOrderIds) || empty($intPlaceOrderId)) {
            return $arrMapOrderList;
        }
        foreach ((array)$arrStockinOrderIds as $intStockinOrderId) {
            $arrMapOrderInfo = [];
            $arrMapOrderInfo['stockin_order_id'] = $intStockinOrderId;
            $arrMapOrderInfo['place_order_id'] = $intPlaceOrderId;
            $arrMapOrderList[] = $arrMapOrderInfo;
        }
        return $arrMapOrderList;
    }

    /**
     * 校验上架单是否已生成
     * @param $strStockinOrderIds
     * @throws Order_BusinessError
     */
    public function checkPlaceOrderExisted($strStockinOrderIds)
    {
        if (empty($strStockinOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::CREATE_PLACE_ORDER_PARAMS_ERROR);
        }
        $arrStockinOrderIds = explode(',', $strStockinOrderIds);
        if (empty($arrStockinOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::CREATE_PLACE_ORDER_PARAMS_ERROR);
        }
        $arrStockinOrderIds = Model_Orm_StockinPlaceOrder::getPlaceOrdersByStockinOrderIds($arrStockinOrderIds);
        if (!empty($arrStockinOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_ALREADY_CREATE);
        }
        //校验是否来自同一个仓库
        $arrStockinOrderInfos = Model_Orm_StockinOrder::getStockinOrderInfosByStockinOrderIds($arrStockinOrderIds);
        $arrMapWarehouseStockinInfos = [];
        foreach ((array)$arrStockinOrderInfos as $arrStockinOrderInfoItem) {
            $intWarehouseId = $arrStockinOrderInfoItem['warehouse_id'];
            $arrMapWarehouseStockinInfos[$intWarehouseId] = true;
        }
        if (count($arrMapWarehouseStockinInfos) > 1) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_FROM_DIFFERENT_WAREHOUSE);
        }
    }

    /**
     * 获取上架单详情
     * @param $intPlaceOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function getPlaceOrderDetail($intPlaceOrderId)
    {
        if (empty($intPlaceOrderId)) {
            return [];
        }
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        if (empty($arrPlaceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_NOT_EXIST);
        }
        $arrPlaceOrderInfo['skus'] = Model_Orm_PlaceOrderSku::getPlaceOrderSkusByPlaceOrderId($intPlaceOrderId);
        $arrStockinOrderIds = Model_Orm_StockinPlaceOrder::getStockinOrderIdsByPlaceOrderId($intPlaceOrderId);
        $arrPlaceOrderInfo['source_order_id'] = implode(',', $arrStockinOrderIds);
        return $arrPlaceOrderInfo;
    }

    /**
     * 获取上架单列表
     * @param $arrInput
     * @return array
     */
    public function getPlaceOrderList($arrInput)
    {
        $arrCondtions = $this->getListConditions($arrInput);
        if (false == $arrCondtions) {
            return [
                'total' => 0,
                'orders' => [],
            ];
        }
        $intLimit = intval($arrInput['page_size']);
        $intOffset = (intval($arrInput['page_num']) - 1) * $intLimit;
        $arrRet = Model_Orm_PlaceOrder::getPlaceOrderList($arrCondtions, $intLimit, $intOffset);
        foreach ((array)$arrRet as $intKey => $arrRetItem) {
            $intPlaceOrderId = $arrRetItem['place_order_id'];
            $arrStockinOrderIds = Model_Orm_StockinPlaceOrder::getStockinOrderIdsByPlaceOrderId($intPlaceOrderId);
            if (!empty($arrStockinOrderIds)) {
                $strStockinOrderIds = implode(',', $arrStockinOrderIds);
                $arrRet[$intKey]['source_order_id'] = $strStockinOrderIds;
            }
        }
        $intTotal = Model_Orm_PlaceOrder::count($arrCondtions);
        return [
            'total' => $intTotal,
            'orders' => $arrRet,
        ];
    }

    /**
     * 获取列表查询条件
     * @param $arrInput
     * @return array
     */
    protected function getListConditions($arrInput)
    {
        $arrConditions = [];
        $arrConditions['is_auto'] = Order_Define_PlaceOrder::PLACE_ORDER_NOT_AUTO;
        if ($arrInput['place_order_status'] == Order_Define_PlaceOrder::STATUS_WILL_PLACE && !empty($arrInput['place_time_start'])
            && !empty($arrInput['place_time_end'])) {
            return false;
        }
        if (!empty($arrInput['place_order_status'])) {
            $arrConditions['place_order_status'] = intval($arrInput['place_order_status']);
        }
        if (!empty($arrInput['warehouse_ids'])) {
            $arrConditions['warehouse_id'] = ['in', $arrInput['warehouse_ids']];
        }
        if (!empty($arrInput['source_order_id'])) {
            if (strlen(strval($arrInput['source_order_id'])) == 4) {
                $arrPlaceOrderIds = Model_Orm_StockinPlaceOrder::
                                        getPlaceOrderIdsByFuzzyStockinOrderId($arrInput['source_order_id']);
            } else {
                $arrPlaceOrderIds = Model_Orm_StockinPlaceOrder::
                                        getPlaceOrdersByStockinOrderIds([$arrInput['source_order_id']]);
            }
            if (empty($arrPlaceOrderIds)) {
                return false;
            }
            $arrConditions['place_order_id'] = ['in', $arrPlaceOrderIds];
        }
        if (!empty($arrInput['place_order_id'])) {
            if (strlen(strval($arrInput['place_order_id'])) == 4) {
                $arrConditions['place_order_id%10000'] = $arrInput['place_order_id'];
            } else {
                $arrConditions['place_order_id'] = $arrInput['place_order_id'];
            }
        }
        if (!empty($arrInput['vendor_id'])) {
            $arrConditions['vendor_id'] = intval($arrInput['vendor_id']);
        }
        if (!empty($arrInput['create_time_start'])) {
            $arrConditions['create_time'][] = ['>=', intval($arrInput['create_time_start'])];
        }
        if (!empty($arrInput['create_time_end'])) {
            $arrConditions['create_time'][] = ['<=', intval($arrInput['create_time_end'])];
        }
        if (!empty($arrInput['place_time_start'])) {
            $arrConditions['update_time'][] = ['>=', intval($arrInput['place_time_start'])];
        }
        if (!empty($arrInput['place_time_end'])) {
            $arrConditions['update_time'][] = ['<=', intval($arrInput['place_time_end'])];
        }
        if (!empty($arrInput['place_time_start'])
            || !empty($arrInput['place_time_end'])) {
            $arrConditions['place_order_status'] = Order_Define_PlaceOrder::STATUS_PLACED;
        }
        return $arrConditions;
    }

    /**
     * 获取上架单打印列表
     * @param $arrPlaceOrderIds
     * @param $strUserName
     * @return array
     * @throws Order_BusinessError
     */
    public function getPlaceOrderPrint($arrPlaceOrderIds, $strUserName)
    {
        if (empty($arrPlaceOrderIds)) {
            return [];
        }
        $arrPlaceOrderInfos = Model_Orm_PlaceOrder::getPlaceOrderInfosByPlaceOrderIds($arrPlaceOrderIds);
        if (empty($arrPlaceOrderInfos)) {
            Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_NOT_EXIST);
        }
        $arrPlaceOrderSkus = Model_Orm_PlaceOrderSku::getPlaceOrderSkusByPlaceOrderIds($arrPlaceOrderIds);
        if (empty($arrPlaceOrderSkus)) {
            return [];
        }
        $arrMapPlaceOrderSkus = Order_Util_Util::arrayToKeyValues($arrPlaceOrderSkus, 'place_order_id');
        foreach ((array)$arrPlaceOrderInfos as $intKey => $arrPlaceOrderInfoItem) {
            $intPlaceOrderId = $arrPlaceOrderInfoItem['place_order_id'];
            if (!isset($arrMapPlaceOrderSkus[$intPlaceOrderId])) {
                continue;
            }
            $arrStockinOrderIds = Model_Orm_StockinPlaceOrder::getStockinOrderIdsByPlaceOrderId($intPlaceOrderId);
            $arrPlaceOrderInfos[$intKey]['source_order_id'] = implode(',', $arrStockinOrderIds);
            $arrPlaceOrderInfos[$intKey]['print_uname'] = $strUserName;
            $arrPlaceOrderInfos[$intKey]['print_time'] = date("Y-m-d H:i:s", time());
            $arrPlaceOrderInfos[$intKey]['skus'] = $arrMapPlaceOrderSkus[$intPlaceOrderId];
            $intTotalAmount = 0;
            foreach ((array)$arrPlaceOrderInfos[$intKey]['skus'] as $arrSkuItem) {
                $intTotalAmount += $arrSkuItem['plan_amount'];
            }
            $arrPlaceOrderInfos[$intKey]['total_amount'] = $intTotalAmount;
        }
        return $arrPlaceOrderInfos;
    }

    /**
     * confirm place order
     * @param $intPlaceOrderId
     * @param $arrPlacedSkus
     * @param $strUserName
     * @param $intUserId
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function confirmPlaceOrder($intPlaceOrderId, $arrPlacedSkus, $strUserName, $intUserId)
    {
        if (empty($intPlaceOrderId) || empty($arrPlacedSkus)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        if (empty($arrPlaceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_NOT_EXIST);
        }
        $intWarehouseId = $arrPlaceOrderInfo['warehouse_id'];
        $intIsDefective = $arrPlaceOrderInfo['is_defective'];
        $arrPlaceOrderSkus = $this->appendPlaceOrderSkuInfo($arrPlacedSkus, $intPlaceOrderId);
        $this->objDaoWprcStock->confirmLocation($intPlaceOrderId, $intWarehouseId, $intIsDefective, $arrPlaceOrderSkus);
        $this->updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus, $strUserName, $intUserId);
        return [];
    }

    /**
     * 上架单上架
     * @param $intPlaceOrderId
     * @param $arrPlacedSkus
     * @return array
     */
    protected function updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus, $strUserName, $intUserId)
    {
        if (empty($intPlaceOrderId) || empty($arrPlacedSkus)) {
            return [];
        }
        $arrMapPlacedSkus = [];
        foreach ((array)$arrPlacedSkus as $arrPlacedSkuItem) {
            $intSkuId = $arrPlacedSkuItem['sku_id'];
            $intExpireDate = $arrPlacedSkuItem['expire_date'];
            if (empty($intSkuId)) {
                continue;
            }
            $arrMapPlacedSkus[$intSkuId . '#' . $intExpireDate][] = $arrPlacedSkuItem;
        }
        Model_Orm_PlaceOrder::getConnection()->transaction(function ()
        use ($intPlaceOrderId, $arrMapPlacedSkus, $strUserName, $intUserId) {
            $boolFlag = Model_Orm_PlaceOrderSku::updatePlaceOrderActualInfo($intPlaceOrderId, $arrMapPlacedSkus);
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_PLACE_FAILED);
            }
            $boolFlag = Model_Orm_PlaceOrder::placeOrder($intPlaceOrderId, $strUserName, $intUserId);
            $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
            if ($arrPlaceOrderInfo['is_auto'] == Order_Define_PlaceOrder::PLACE_ORDER_IS_AUTO) {
                $arrStockinOrderIds = Model_Orm_StockinPlaceOrder::getStockinOrderIdsByPlaceOrderId($intPlaceOrderId);
                Model_Orm_StockinOrder::placeStockinOrder($arrStockinOrderIds, Order_Define_PlaceOrder::PLACE_ORDER_IS_AUTO);
            }
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_PLACE_FAILED);
            }
        });
    }

    /**
     * 预约入库是否生成上架单
     * @param $intStockinOrderId
     * @return bool
     */
    protected function isPlacedOrderForReserve($intStockinOrderId) {
        $arrStockinOrderInfo = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($intStockinOrderId);
        return $arrStockinOrderInfo['is_placed_order'];
    }

    /**
     * 预约入库单加入is_placed_order字段
     * @param $arrStockinOrderList
     * @return mixed
     */
    public function appendIsPlacedOrderToStockinOrderList($arrStockinOrderList) {
        if (empty($arrStockinOrderList)) {
            return $arrStockinOrderList;
        }
        foreach ((array)$arrStockinOrderList as $intKey => $arrStockinOrderInfo) {
            $intStockinOrderId = $arrStockinOrderInfo['stockin_order_id'];
            $arrStockinOrderList[$intKey]['is_placed_order'] = $this->isPlacedOrderForReserve($intStockinOrderId);
            $intIsAuto = $this->IsAutoPlacedOrder($intStockinOrderId);
            if ($intIsAuto == Order_Define_StockinOrder::STOCKIN_AUTO_PLACED) {
                $arrStockinOrderList[$intKey]['is_placed_order'] = Order_Define_StockinOrder::STOCKIN_AUTO_PLACED;
            }
        }
        return $arrStockinOrderList;
    }

    /**
     * 入库单列表加入是否自动上架标识
     * @param $arrStockinOrderList
     * @return mixed
     */
    public function checkIsAutoPlacedToStockinOrderList($arrStockinOrderList) {
        if (empty($arrStockinOrderList)) {
            return $arrStockinOrderList;
        }
        foreach ((array)$arrStockinOrderList as $intKey => $arrStockinOrderInfo) {
            $intStockinOrderId = $arrStockinOrderInfo['stockin_order_id'];
            $intIsAuto = $this->IsAutoPlacedOrder($intStockinOrderId);
            if ($intIsAuto == Order_Define_StockinOrder::STOCKIN_AUTO_PLACED) {
                $arrStockinOrderList[$intKey]['is_placed_order'] = Order_Define_StockinOrder::STOCKIN_AUTO_PLACED;
            }
        }
        return $arrStockinOrderList;
    }

    /**
     * 判断入库单是否自动生成上架单
     * @param $intStockinOrderId
     * @return bool
     */
    public function IsAutoPlacedOrder($intStockinOrderId) {
        $arrStockinOrderIds[] = $intStockinOrderId;
        $arrPlaceOrderIds = Model_Orm_StockinPlaceOrder::getPlaceOrdersByStockinOrderIds($arrStockinOrderIds);
        if (empty($arrPlaceOrderIds)) {
            return false;
        }
        $intPlaceOrderId = $arrPlaceOrderIds[0];
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        return $arrPlaceOrderInfo['is_auto'];
    }

    /**
     * 在确认上架单sku列表中增加sku信息
     * @param $arrPlacedSkus
     * @param $intPlaceOrderId
     * @return array
     */
    protected function appendPlaceOrderSkuInfo($arrPlacedSkus, $intPlaceOrderId) {
        if (empty($arrPlacedSkus)) {
            return [];
        }
        $arrPlaceOrderSkus = Model_Orm_PlaceOrderSku::getPlaceOrderSkusByPlaceOrderId($intPlaceOrderId);
        //构造一个skuid和expire_date的map
        $arrMapSkuExpireDate = [];
        foreach ((array)$arrPlacedSkus as $arrPlacedSkusItem) {
            $intExpireDate = $arrPlacedSkusItem['expire_date'];
            $intSkuId = $arrPlacedSkusItem['sku_id'];
            $arrMapSkuExpireDate[$intSkuId .'#'. $intExpireDate][] = $arrPlacedSkusItem;
        }
        //在place order sku中增加sku属性
        foreach ((array)$arrPlaceOrderSkus as $intKey => $arrPlaceOrderSkuItem) {
            $intSkuId = $arrPlaceOrderSkuItem['sku_id'];
            $intExpireDate = $arrPlaceOrderSkuItem['expire_date'];
            $arrActualInfo = $arrMapSkuExpireDate[$intSkuId.'#'.$intExpireDate];
            $arrPlaceOrderSkus[$intKey]['actual_info'] = $arrActualInfo;
        }
        return $arrPlaceOrderSkus;
    }

    /**
     * 获取仓库是否开启库区库位校验
     * @param $intWarehouseId
     * @return mixed
     */
    protected function getWarehouseLocationTag($intWarehouseId)
    {
        $arrWarehouseInfo = $this->objDaoWrpcWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        return $arrWarehouseInfo['storage_location_tag'];
    }

    /**
     * sug库位信息
     * @param $intWarehouseId
     * @param $strLocationCode
     * @param $intIsDefault
     * @return array
     */
    public function sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefaultStore)
    {
        $objDaoWrpcWarehouseStorage = new Dao_Wrpc_Warehouse(Order_Define_Wrpc::NWMS_WAREHOUSE_STORAGE_SERVICE_NAME);
        $arrLocation = $objDaoWrpcWarehouseStorage->sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefaultStore);
        return $arrLocation;
    }
}
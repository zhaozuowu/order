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
        if (empty($arrStockinOrderInfo)) {
            return [];
        }
        //创建上架单
        $arrSplitOrderInfo = $this->splitStockinOrderByQuality($arrStockinOrderInfo);
        list($arrOrderList, $arrSkuList, $arrMapOrderList) =
            $this->getCreateParams($arrSplitOrderInfo);
        Model_Orm_PlaceOrder::getConnection()->transaction(function ()
        use ($arrOrderList, $arrSkuList, $arrMapOrderList, $arrStockinOrderIds) {
            Model_Orm_PlaceOrder::batchInsert($arrOrderList);
            Model_Orm_PlaceOrderSku::batchInsert($arrSkuList);
            Model_Orm_StockinPlaceOrder::batchInsert($arrMapOrderList);
            $boolFlag = Model_Orm_StockinOrder::placeStockinOrder($arrStockinOrderIds);
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_CREATE_FAILED);
            }
        });
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
        if (1 == count($arrStockinOrderIds)) {
            $arrStockinInfoDb = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($arrStockinOrderIds[0]);
            if (empty($arrStockinInfoDb)) {
                return [];
            }
            $arrStockinInfo['vendor_id'] = $arrStockinInfoDb['vendor_id'];
            $arrStockinInfo['vendor_name'] = $arrStockinInfoDb['vendor_name'];
            $arrStockinInfo['warehouse_id'] = $arrStockinInfoDb['warehouse_id'];
            $arrStockinInfo['warehouse_name'] = $arrStockinInfoDb['warehouse_name'];
            $arrStockinInfo['stockin_order_type'] = $arrStockinInfoDb['stockin_order_type'];
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
        $intLocationTag = $this->getWarehouseLocationTag($arrInput['warehouse_id']);
        if (Order_Define_Warehouse::STORAGE_LOCATION_TAG_DISABLE
            == $intLocationTag) {
            $arrOrderInfo['place_order_status'] = Order_Define_PlaceOrder::STATUS_PLACED;
        }
        //非良品订单信息
        if (!empty($arrInput['bad_skus'])) {
            $arrBadSkuOrderInfo['place_order_id'] = Order_Util_Util::generatePlaceOrderId();
            $arrBadSkuOrderInfo['is_defective'] = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_BAD;
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
            $arrGoodSkuOrderInfo['is_defective'] = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_GOOD;
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
        if (!empty($arrInput['place_order_status'])) {
            $arrConditions['place_order_status'] = intval($arrInput['place_order_status']);
        }
        if (!empty($arrInput['source_order_id'])) {
            $arrPlaceOrderIds = Model_Orm_StockinPlaceOrder::
                                    getPlaceOrdersByStockinOrderIds([$arrInput['source_order_id']]);
            $arrConditions['place_order_id'] = ['in', $arrPlaceOrderIds];
        }
        if (!empty($arrInput['place_order_id'])) {
            $arrConditions['place_order_id'] = intval($arrInput['place_order_id']);
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
     * 确认上架单
     * @param $intPlaceOrderId
     * @param $arrPlacedSkus
     * @return array
     * @throws Order_BusinessError
     */
    public function confirmPlaceOrder($intPlaceOrderId, $arrPlacedSkus)
    {
        if (empty($intPlaceOrderId) || empty($arrPlacedSkus)) {
            return [];
        }
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        if (empty($arrPlaceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_NOT_EXIST);
        }
        $intWarehouseId = $arrPlaceOrderInfo['warehouse_id'];
        $intIsDefective = $arrPlaceOrderInfo['is_defective'];
        $arrPlaceOrderSkus = $this->appendPlaceOrderSkuInfo($arrPlacedSkus, $intPlaceOrderId);
        $this->objDaoWprcStock->confirmLocation($intPlaceOrderId, $intWarehouseId, $intIsDefective, $arrPlaceOrderSkus);
        $this->updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus);
    }

    /**
     * 上架单上架
     * @param $intPlaceOrderId
     * @param $arrPlacedSkus
     * @return array
     */
    protected function updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus)
    {
        if (empty($intPlaceOrderId) || empty($arrPlacedSkus)) {
            return [];
        }
        $arrMapPlacedSkus = [];
        foreach ((array)$arrPlacedSkus as $arrPlacedSkuItem) {
            $intSkuId = $arrPlacedSkuItem['sku_id'];
            if (empty($intSkuId)) {
                continue;
            }
            $arrMapPlacedSkus[$intSkuId][] = $arrPlacedSkuItem;
        }
        Model_Orm_PlaceOrder::getConnection()->transaction(function () use ($intPlaceOrderId, $arrPlacedSkus) {
            $boolFlag = Model_Orm_PlaceOrderSku::updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus);
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_PLACE_FAILED);
            }
            $boolFlag = Model_Orm_PlaceOrder::placeOrder($intPlaceOrderId);
            if (!$boolFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PLACE_ORDER_PLACE_FAILED);
            }
        });
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
        if (empty($arrPlaceOrderSkus)) {

        }
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
    public function sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefault)
    {
        $objDaoWrpcWarehouseStorage = new Dao_Wrpc_Warehouse(Order_Define_Wrpc::NWMS_WAREHOUSE_STORAGE_SERVICE_NAME);
        $arrLocation = $objDaoWrpcWarehouseStorage->sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefault);
        return $arrLocation;
    }
}
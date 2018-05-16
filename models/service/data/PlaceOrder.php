<?php
/**
 * @name PlaceOrder.php
 * @desc PlaceOrder.php
 * @author yu.jin03@ele.me
 */

class Service_Data_PlaceOrder
{
    /**
     * 创建上架单
     * @param $intStockinOrderId
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
        foreach ((array)$arrInput['skus'] as $arrSkuItem) {
            $arrPlaceOrderSkuInfo = [];
            $arrPlaceOrderSkuInfo['sku_id'] = $arrSkuItem['sku_id'];
            $arrPlaceOrderSkuInfo['sku_name'] = $arrSkuItem['sku_name'];
            $arrPlaceOrderSkuInfo['upc_id'] = $arrSkuItem['upc_id'];
            $arrPlaceOrderSkuInfo['upc_unit'] = $arrSkuItem['upc_unit'];
            $arrPlaceOrderSkuInfo['upc_unit_num'] = $arrSkuItem['upc_unit_num'];
            $arrPlaceOrderSkuInfo['sku_net'] = $arrSkuItem['sku_net'];
            $arrPlaceOrderSkuInfo['sku_net_unit'] = $arrSkuItem['sku_net_unit'];
            $arrPlaceOrderSkuInfo['sku_effect_time'] = $arrSkuItem['sku_effect_time'];
            if ($arrSkuItem['stockin_extra_info']['good_amount'] > 0) {
                $arrPlaceOrderSkuInfo['plan_amount'] =
                    $arrSkuItem['stockin_extra_info']['good_amount'];
                $arrRetOrderInfo['good_skus'][] = $arrPlaceOrderSkuInfo;
            }
            if ($arrSkuItem['stockin_extra_info']['bad_amount'] > 0) {
                $arrPlaceOrderSkuInfo['plan_amount'] =
                    $arrSkuItem['stockin_extra_info']['bad_amount'];
                $arrRetOrderInfo['bad_skus'][] = $arrPlaceOrderSkuInfo;
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
        //非良品订单信息
        if (!empty($arrInput['good_skus'])) {
            $arrBadSkuOrderInfo['place_order_id'] = Order_Util_Util::generatePlaceOrderId();
            $arrBadSkuOrderInfo['vendor_id'] = intval($arrInput['vendor_id']);
            $arrBadSkuOrderInfo['vendor_name'] = strval($arrInput['vendor_name']);
            $arrBadSkuOrderInfo['place_order_status'] = Order_Define_PlaceOrder::STATUS_WILL_PLACE;
            $arrBadSkuOrderInfo['is_defective'] = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_BAD;
            $arrOrderList[] = $arrBadSkuOrderInfo;
            $arrInput['bad_skus']['place_order_id'] = intval($arrBadSkuOrderInfo['place_order_id']);
            $arrSkuList[] = $arrInput['bad_skus'];
            $arrGoodMapOrderList = $this->getMapOrderList($arrInput['stockin_order_ids'], $arrInput['place_order_id']);
            $arrMapOrderList = array_merge($arrMapOrderList, $arrGoodMapOrderList);
        }
        //良品订单信息
        if (!empty($arrInput['bad_skus'])) {
            $arrGoodSkuOrderInfo['place_order_id'] = Order_Util_Util::generatePlaceOrderId();
            $arrGoodSkuOrderInfo['vendor_id'] = $arrInput['vendor_id'];
            $arrGoodSkuOrderInfo['vendor_name'] = $arrInput['vendor_name'];
            $arrGoodSkuOrderInfo['is_defective'] = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_GOOD;
            $arrOrderList[] = $arrGoodSkuOrderInfo;
            $arrInput['good_skus']['place_order_id'] = $arrGoodSkuOrderInfo['place_order_id'];
            $arrSkuList[] = $arrInput['good_skus'];
            $arrBadMapOrderList = $this->getMapOrderList($arrInput['stockin_order_ids'], $arrInput['place_order_id']);
            $arrMapOrderList = array_merge($arrMapOrderList, $arrBadMapOrderList);
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
     */
    public function getPlaceOrderDetail($intPlaceOrderId)
    {
        if (empty($intPlaceOrderId)) {
            return [];
        }
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        $arrPlaceOrderInfo['skus'] = Model_Orm_PlaceOrderSku::getPlaceOrderSkusByPlaceOrderId($intPlaceOrderId);
        $arrPlaceOrderInfo['stockin_order_ids'] = Model_Orm_StockinPlaceOrder::getStockinOrderIdsByPlaceOrderId($intPlaceOrderId);
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
        return $arrConditions;
    }

    public function getPlaceOrderPrint($arrStockoutOrderIds)
    {

    }

}
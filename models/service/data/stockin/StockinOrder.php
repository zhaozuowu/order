<?php
/**
 * @name Service_Data_Stockin_StockinOrder
 * @desc Service_Data_Stockin_StockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Stockin_StockinOrder
{
    /**
     * calculate stock in order sku info
     * @param int $intStockinOrderId
     * @param array $sourceOrderSkuInfo
     * @param array $arrSkuInfo
     * @throws Order_BusinessError
     * @return array
     */
    private function calculateStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo)
    {
        $arrDbStockinOrderSkuExtraInfo = [];
        // amount
        $intTotalAmount = 0;
        $i = 0;
        foreach ($arrSkuInfo['real_stockin_info'] as $arrRealStockinInfo) {
            $arrDbStockinOrderSkuExtraInfo[] = [
                'amount' => $arrRealStockinInfo['amount'],
                'expire_date' => $arrRealStockinInfo['expire_date'],
            ];
            $i++;
            $intTotalAmount += intval($arrRealStockinInfo['amount']);
            if ($i >= Order_Define_StockinOrder::STOCKIN_SKU_EXP_DATE_MAX) {
                // @todo stock in info too much
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
        }
        if ($intTotalAmount > $sourceOrderSkuInfo['reserve_order_sku_plan_amount']) {
            // @todo stock in order sku amount must smaller than reserve order
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        return [
            'stockin_order_id' => $intStockinOrderId,
            'sku_id' => $sourceOrderSkuInfo['sku_id'],
            'upc_id' => $sourceOrderSkuInfo['upc_id'],
            'upc_unit' => $sourceOrderSkuInfo['upc_unit'],
            'upc_unit_num' => $sourceOrderSkuInfo['upc_unit_num'],
            'sku_name' => $sourceOrderSkuInfo['sku_name'],
            'sku_net' => $sourceOrderSkuInfo['sku_net'],
            'sku_net_unit' => $sourceOrderSkuInfo['sku_net_unit'],
            'sku_net_gram' => $sourceOrderSkuInfo['sku_net_gram'],
            'sku_price' => $sourceOrderSkuInfo['sku_price'],
            'sku_price_tax' => $sourceOrderSkuInfo['sku_price_tax'],
            'stockin_order_sku_total_price' => $intTotalAmount * $sourceOrderSkuInfo['sku_price'],
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $sourceOrderSkuInfo['sku_price_tax'],
            'reserve_order_sku_plan_amount' => $sourceOrderSkuInfo['reserve_order_sku_plan_amount'],
            'stockin_order_sku_real_amount' => $intTotalAmount,
            'stockin_order_sku_extra_info' => json_encode($arrDbStockinOrderSkuExtraInfo),
        ];
    }

    /**
     * get db stock in skus
     * @param int $intStockinOrderId
     * @param array $arrReserveOrderSkus
     * @param array $arrSkuInfoList
     * @return array
     * @throws Order_BusinessError
     */
    private function getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList)
    {
        // pre treat sku
        $arrHashReserveOrderSkus = [];
        foreach ($arrReserveOrderSkus as $arrSku) {
            $arrHashReserveOrderSkus[$arrSku['sku_id']] = $arrSku;
        }
        $arrDbSkuInfoList = [];
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            if (!isset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']])) {
                // @todo sku id not in purchase order or sku id repeat
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
            $arrReserveOrderSku = $arrHashReserveOrderSkus[$arrSkuInfo['sku_id']];
            $arrSkuRow = $this->calculateStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo);
            $arrDbSkuInfoList[] = $arrSkuRow;
            unset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']]);
        }
        return $arrDbSkuInfoList;
    }

    /**
     * calculate total sku amount
     * @param array $arrSkus
     * @return int
     */
    private function calculateTotalSkuAmount($arrSkus)
    {
        $intResult = 0;
        foreach ($arrSkus as $arrSkus) {
            $intResult += intval($arrSkus['stockin_order_sku_real_amount']);
        }
        return $intResult;
    }

    /**
     * @param $arrReserveOrderInfo
     * @param $arrReserveOrderSkus
     * @param $intWarehouseId
     * @param $strStockinOrderRemark
     * @param $arrSkuInfoList
     * @param $intCreatorId
     * @param $strCreatorName
     * @return int
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function createStockinOrderReserve($arrReserveOrderInfo, $arrReserveOrderSkus, $intWarehouseId, $strStockinOrderRemark, $arrSkuInfoList,
                                              $intCreatorId, $strCreatorName)
    {
        $intStockinOrderId = Order_Util_Util::generateStockinOrderCode();
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList);
        $intStockinOrderRealAmount = $this->calculateTotalSkuAmount($arrDbSkuInfoList);
        $intStockinOrderType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
        $intSourceOrderId = intval($arrReserveOrderInfo['reserve_order_id']);
        //@todo
        $arrSourceInfo = [];
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH;
        $intWarehouseId = intval($intWarehouseId);
        //@todo
        $strWarehouseName = '';
        $intStockinTime = time();
        $intStockinOrderPlanAmount = $arrReserveOrderInfo['reserve_order_plan_amount'];
        $intStockinOrderCreatorId = intval($intCreatorId);
        $strStockinOrderCreatorName = strval($strCreatorName);
        $strStockinOrderRemark = strval($strStockinOrderRemark);
        Model_Orm_StockinOrder::getConnection()->transaction(function () use (
            $intStockinOrderId, $intStockinOrderType,
            $intSourceOrderId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $strWarehouseName, $intStockinTime,
            $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
            $strStockinOrderRemark, $arrDbSkuInfoList
        ) {
            Model_Orm_StockinOrder::createStockinOrder(
                $intStockinOrderId, $intStockinOrderType,
                $intSourceOrderId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $strWarehouseName, $intStockinTime,
                $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
                $strStockinOrderRemark);
            Model_Orm_StockinOrderSku::batchCreateStockinOrderSku($arrDbSkuInfoList, $intStockinOrderId);
            // @todo event track
            if (!$this->notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $arrDbSkuInfoList)) {
                Order_Error::throwException(Order_Error_Code::ERR__RAL_ERROR);
            }
            return $intStockinOrderId;
        });
        return $intStockinOrderId;
    }

    /**
     * call stock
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intWarehouseId
     * @param array $arrDbSkuInfoList
     * @return bool
     */
    public function notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $arrDbSkuInfoList)
    {
        return true;
    }


    /**
     * 获取入库单列表（分页）
     *
     * @param $strStockinOrderType
     * @param $strWarehouseId
     * @param $intSourceSupplierId
     * @param $strSourceOrderId
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return mixed
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function getStockinOrderList(
        $strStockinOrderType,
        $strWarehouseId,
        $intSourceSupplierId,
        $strSourceOrderId,
        $arrCreateTime,
        $arrOrderPlanTime,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        $arrStockinOrderType = Order_Util::extractIntArray($strStockinOrderType);
        // 校验入库单类型参数是否合法
        if (false === Model_Orm_StockinOrder::isStockinOrderTypeCorrect($arrStockinOrderType)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        $arrWarehouseId = Order_Util::extractIntArray($strWarehouseId);

        // 拆解出关联入库单号,较复杂的订单号ID场景处理，根据入库单类型进行，如果类型和查询入库单类型不匹配抛出参数异常
        // 订单号ID获取，分解未参数类型及单号[source_order_id, source_order_type]
        $arrSourceOrderIdInfo = $this->getSourceOrderId($strSourceOrderId, $arrStockinOrderType);

        $arrCreateTime['start'] = intval($arrCreateTime['start']);
        $arrCreateTime['end'] = intval($arrCreateTime['end']);

        $arrOrderPlanTime['start'] = intval($arrOrderPlanTime['start']);
        $arrOrderPlanTime['end'] = intval($arrOrderPlanTime['end']);

        $arrStockinTime['start'] = intval($arrStockinTime['start']);
        $arrStockinTime['end'] = intval($arrStockinTime['end']);

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrCreateTime['start'],
                $arrCreateTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrOrderPlanTime['start'],
                $arrOrderPlanTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrStockinTime['start'],
                $arrStockinTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        return Model_Orm_StockinOrder::getStockinOrderList(
            $arrStockinOrderType,
            $arrWarehouseId,
            $intSourceSupplierId,
            $arrSourceOrderIdInfo,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }

    /**
     * 分解获取关联入库单的单号，只处理ASN和SOO两种订单号，否则返回null
     * 如果订单号前缀类型不在给定的数组内则抛出参数错误异常
     * [source_order_id, source_order_type]
     *
     * @param $strSourceOrderId
     * @param $arrStockinOrderType
     * @return null|array[source_order_id, source_order_type]
     * @throws Order_Error
     */
    private function getSourceOrderId($strSourceOrderId, $arrStockinOrderType)
    {
        if(empty($strSourceOrderId)){
            return null;
        }

        // preg_match('/^ASN\d{13}$/', $strSourceOrderId)
        if(!empty(preg_match('/^'. Nscm_Define_OrderPrefix::ASN .'\d{13}$/', $strSourceOrderId))){
            if(false === Order_Util::valueIsInArray(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE, $arrStockinOrderType)){
                Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimReserveOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        // preg_match('/^SOO\d{13}$/', $strSourceOrderId)
        if(!empty(preg_match('/^' . Nscm_Define_OrderPrefix::SOO . '\d{13}$/', $strSourceOrderId))){
            if(false === Order_Util::valueIsInArray(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT, $arrStockinOrderType)){
                Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimStockoutOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        return null;
    }
}
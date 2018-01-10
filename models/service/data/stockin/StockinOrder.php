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
     * @throws Order_Error
     * @return array
     */
    private function formatStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo, $intOrderType = 1)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intOrderType) {
            $intSkuPrice = $sourceOrderSkuInfo['sku_price'];
            $intSkuPriceTax = $sourceOrderSkuInfo['sku_price_tax'];
            $intPlanAmount =  $sourceOrderSkuInfo['reserve_order_sku_plan_amount'];
        } else {
            $intSkuPrice = $sourceOrderSkuInfo['send_price'];
            $intSkuPriceTax = $sourceOrderSkuInfo['send_price_tax'];
            $intPlanAmount = $sourceOrderSkuInfo['pickup_amount'];
        }
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
            if ($i > Order_Define_StockinOrder::STOCKIN_SKU_EXP_DATE_MAX) {
                // max expire time 2
                Order_BusinessError::throwException(Order_Error_Code::SKU_TOO_MUCH);
            }
        }
        if ($intTotalAmount > $intPlanAmount) {
            // stock in order sku amount must smaller than reserve order
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_AMOUNT_TOO_MUCH);
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
            'sku_price' => $intSkuPrice,
            'sku_price_tax' => $intSkuPriceTax,
            'stockin_order_sku_total_price' => $intTotalAmount * $intSkuPrice,
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $intSkuPriceTax,
            'reserve_order_sku_plan_amount' => $intPlanAmount,
            'stockin_order_sku_real_amount' => $intTotalAmount,
            'stockin_order_sku_extra_info' => json_encode($arrDbStockinOrderSkuExtraInfo),
        ];
    }

    /**
     * calculate total sku amount
     * @param array $arrDbSkus
     * @return int
     */
    private function calculateTotalSkuAmount($arrDbSkus)
    {
        $intResult = 0;
        foreach ($arrDbSkus as $arrSku) {
            $intResult += intval($arrSku['stockin_order_sku_real_amount']);
        }
        return $intResult;
    }

    /**
     * get db stock in skus
     * @param int $intStockinOrderId
     * @param array $arrReserveOrderSkus
     * @param array $arrSkuInfoList
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
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
                // sku id not in purchase order or sku id repeat
                Order_BusinessError::throwException(Order_Error_Code::SKU_ID_NOT_EXIST_OR_SKU_ID_REPEAT);
            }
            $arrReserveOrderSku = $arrHashReserveOrderSkus[$arrSkuInfo['sku_id']];
            $arrSkuRow = $this->formatStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo);
            $arrDbSkuInfoList[$arrSkuInfo['sku_id']] = $arrSkuRow;
            unset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']]);
        }
        return $arrDbSkuInfoList;
    }

    /**
     * calculate total price
     * @param array $arrDbSkus
     * @return int
     */
    private function calculateTotalPrice($arrDbSkus)
    {
        $intResult = 0;
        foreach ($arrDbSkus as $arrSku) {
            $intResult += $arrSku['stockin_order_sku_total_price'];
        }
        return $intResult;
    }

    /**
     * calculate total price tax
     * @param array $arrDbSkus
     * @return int
     */
    private function calculateTotalPriceTax($arrDbSkus)
    {
        $intResult = 0;
        foreach ($arrDbSkus as $arrSku) {
            $intResult += $arrSku['stockin_order_sku_total_price_tax'];
        }
        return $intResult;
    }

    /**
     * get source info
     * @param array $arrSourceOrderInfo
     * @param int $intType
     * @return array
     */
    private function getSourceInfo($arrSourceOrderInfo, $intType)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intType) {
            $arrSourceInfo = [
                'vendor_id' => $arrSourceOrderInfo['vendor_id'],
                'vendor_name' => $arrSourceOrderInfo['vendor_name'],
                'vendor_contactor' => $arrSourceOrderInfo['vendor_contactor'],
                'vendor_mobile' => $arrSourceOrderInfo['vendor_mobile'],
                'vendor_email' => $arrSourceOrderInfo['vendor_email'],
                'vendor_address' => $arrSourceOrderInfo['vendor_address'],
            ];
        } else {
            $arrSourceInfo = [
                'customer_id' => $arrSourceOrderInfo['customer_id'],
                'customer_name' => $arrSourceOrderInfo['customer_name'],
                'customer_contactor' => $arrSourceOrderInfo['customer_contactor'],
                'customer_contact' => $arrSourceOrderInfo['customer_contact'],
                'customer_address' => $arrSourceOrderInfo['customer_address'],
            ];
        }
        return $arrSourceInfo;
    }

    /**
     * @param array $arrSourceOrderInfo
     * @param array $arrSourceOrderSkus
     * @param int $intWarehouseId
     * @param string $strStockinOrderRemark
     * @param array $arrSkuInfoList
     * @param int $intCreatorId
     * @param string $strCreatorName
     * @param int $intType
     * @return int
     * @throws Exception
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId, $strStockinOrderRemark,
                                       $arrSkuInfoList, $intCreatorId, $strCreatorName, $intType)
    {

        if (!isset(Order_Define_StockinOrder::STOCKIN_ORDER_TYPES[$intType])) {
            // order type error
            Order_Error::throwException(Order_Error_Code::SOURCE_ORDER_TYPE_ERROR);
        }
        $intStockinOrderId = Order_Util_Util::generateStockinOrderCode();
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrSourceOrderSkus, $arrSkuInfoList);
        $intStockinOrderRealAmount = $this->calculateTotalSkuAmount($arrDbSkuInfoList);
        $intStockinOrderTotalPrice = $this->calculateTotalPrice($arrDbSkuInfoList);
        $intStockinOrderTotalPriceTax = $this->calculateTotalPriceTax($arrDbSkuInfoList);
        $intStockinOrderType = intval($intType);
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockinOrderType) {
            $intSourceOrderId = intval($arrSourceOrderInfo['reserve_order_id']);
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['reserve_order_plan_amount'];
            $intSourceSupplierId = $arrSourceOrderInfo['vendor_id'];
            $intReserveOrderPlanTime = $arrSourceOrderInfo['reserve_Order_plan_time'];
        } else {
            $intSourceOrderId = intval($arrSourceOrderInfo['stockout_order_id']);
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['stockout_order_pickup_amount'];
            $intSourceSupplierId = $arrSourceOrderInfo['customer_id'];
            $intReserveOrderPlanTime = 0;
        }
        $arrSourceInfo = $this->getSourceInfo($arrSourceOrderInfo, $intType);
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH;
        $intWarehouseId = intval($intWarehouseId);
        $objDaoWarehouse = new Dao_Ral_Order_Warehouse();
        $arrWarehouseInfo = $objDaoWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        if (empty($arrWarehouseInfo)) {
            Order_Error::throwException(Order_Error_Code::RAL_ERROR);
        }
        $strWarehouseName = $arrWarehouseInfo['warehouse_name'];
        $intCityId = $arrWarehouseInfo['city']['id'];
        $strCityName = $arrWarehouseInfo['city']['name'];
        $intStockinTime = time();
        $intStockinOrderCreatorId = intval($intCreatorId);
        $strStockinOrderCreatorName = strval($strCreatorName);
        $strStockinOrderRemark = strval($strStockinOrderRemark);
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($intStockinOrderId, $intStockinOrderType,
            $intSourceOrderId, $intSourceSupplierId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId,
            $strWarehouseName, $intCityId, $strCityName, $intStockinTime, $intReserveOrderPlanTime,
            $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
            $strStockinOrderRemark, $arrDbSkuInfoList, $intStockinOrderTotalPrice, $intStockinOrderTotalPriceTax) {
            $intVendorId = $intStockinOrderType == Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE ? $intSourceSupplierId : 0;
            $arrStock = $this->notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $intVendorId, $arrDbSkuInfoList);
            $intStockinBatchId = $arrStock['stockin_batch_id'];
            Model_Orm_StockinOrder::createStockinOrder(
                $intStockinOrderId,
                $intStockinOrderType,
                $intSourceOrderId,
                $intStockinBatchId,
                $intSourceSupplierId,
                $strSourceInfo,
                $intStockinOrderStatus,
                $intCityId,
                $strCityName,
                $intWarehouseId,
                $strWarehouseName,
                $intStockinTime,
                $intReserveOrderPlanTime,
                $intStockinOrderPlanAmount,
                $intStockinOrderRealAmount,
                $intStockinOrderCreatorId,
                $strStockinOrderCreatorName,
                $strStockinOrderRemark,
                $intStockinOrderTotalPrice,
                $intStockinOrderTotalPriceTax);
            Model_Orm_StockinOrderSku::batchCreateStockinOrderSku($arrDbSkuInfoList, $intStockinOrderId);
            if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockinOrderType) {
                $ormStockinOrder = Model_Orm_ReserveOrder::findReserveOrder($intSourceOrderId);
                if (empty($ormStockinOrder)) {
                    Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
                }
                $ormStockinOrder->syncStockinInfo($intStockinOrderId, $intStockinTime, $intStockinOrderRealAmount,
                    Order_Define_ReserveOrder::STATUS_STOCKED);
                $arrOrmStockinOrderSkus = Model_Orm_ReserveOrderSku::findAllStockinSku($intSourceOrderId);
                foreach ($arrOrmStockinOrderSkus as $ormStockinOrderSku) {
                    if (isset($arrDbSkuInfoList[$ormStockinOrderSku->sku_id])) {
                        $intRealAmount = $arrDbSkuInfoList[$ormStockinOrderSku->sku_id]['stockin_order_sku_real_amount'];
                        $strExtraInfo = $arrDbSkuInfoList[$ormStockinOrderSku->sku_id]['stockin_order_sku_extra_info'];
                    } else {
                        $intRealAmount = 0;
                        $strExtraInfo = '[]';
                    }
                    $ormStockinOrderSku->syncStockinSkuInfo($intRealAmount, $strExtraInfo);
                }
            }
            return $intStockinOrderId;
        });
        // notify statistics
        $intTable = Order_Statistics_Type::STOCKIN_MAP[$intStockinOrderType];
        $intType = Order_Statistics_Type::ACTION_CREATE;
        Dao_Ral_Statistics::syncStatistics($intTable, $intType, $intStockinOrderId);
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockinOrderType) {
            $this->notifyNscm($intSourceOrderId, $intStockinTime, $arrDbSkuInfoList);
        }
        // @todo log
        return $intStockinOrderId;
    }

    /**
     * format nscm skus
     * @param array $arrDbSkus
     * @return array
     */
    private function formatNscmSkus($arrDbSkus)
    {
        $arrRet = [];
        foreach ($arrDbSkus as $arrSku)
        {
            $arrRet[] = [
                'upc' => $arrSku['upc_id'],
                'real_amount' => $arrSku['stockin_order_sku_real_amount'],
                'unit' => Order_Define_Sku::UPC_UNIT_MAP[$arrSku['upc_unit']],
            ];
        }
        return $arrRet;
    }

    /**
     * @param int $intReserveOrderId
     * @param int $intStockinTime
     * @param array $arrDbSkus
     */
    public function notifyNscm($intReserveOrderId, $intStockinTime, $arrDbSkus)
    {
        Dao_Ral_SyncInbound::syncInbound($intReserveOrderId, Order_Define_StockinOrder::NSCM_SURE_STOCKIN,
            $intStockinTime, $this->formatNscmSkus($arrDbSkus));
    }

    /**
     * calculate expire
     * @param array $arrDbSku<p>
     * sku row from table stockin_order_sku
     * </p>
     * @return array
     */
    private function calculateExpire($arrDbSku)
    {
        $arrStockinOrderSkuExtraInfo = json_decode($arrDbSku['stockin_order_sku_extra_info'], true);
        $arrBatchInfo = [];
        foreach ($arrStockinOrderSkuExtraInfo as $skuRow) {
            if (Order_Define_Sku::SKU_EFFECT_TYPE_PRODUCT == $arrDbSku['sku_effect_type']) {
                $intProductionTime = intval($skuRow['expire_date']);
                $intExpireTime = $intProductionTime + intval($arrDbSku['sku_effect_day']) * 86400;
            } else {
                $intExpireTime = intval($skuRow['expire_date']) + 86400;
                $intProductionTime = $intExpireTime - intval($arrDbSku['sku_effect_day']) * 86400;
            }
            $arrBatchInfo[] = [
                'expire_time' => $intExpireTime,
                'production_time' => $intProductionTime,
                'amount'      => $skuRow['amount'],
            ];
        }
        return $arrBatchInfo;
    }

    /**
     * call stock
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intWarehouseId
     * @param int $intVendorId
     * @param array $arrDbSkuInfoList
     * @return int
     * @throws Nscm_Exception_Business
     * @throws Order_BusinessError
     */
    public function notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $intVendorId, $arrDbSkuInfoList)
    {
        $arrStockinSkuInfo = [];
        foreach ($arrDbSkuInfoList as $row) {
            $arrStockinSkuInfo[] = [
                'sku_id'        => $row['sku_id'],
                'unit_price'    => $row['sku_price'],
                'unit_price_tax'=> $row['sku_price_tax'],
                'batch_info'    => $this->calculateExpire($row),
            ];
        }
        $arrInputParam = [
            'stockin_order_id'      => $intStockinOrderId,
            'stockin_order_type'    => $intStockinOrderType,
            'warehouse_id'          => $intWarehouseId,
            'vendor_id'             => $intVendorId,
            'stockin_sku_info'      => $arrStockinSkuInfo,
        ];
        Bd_Log::trace('call nwms stock, request: ' . json_encode($arrInputParam));
        $arrRet = Nscm_Service_Stock::stockin($arrInputParam);
        Bd_Log::trace('call nwms stock, response: ' . json_encode($arrRet));
        if (0 != $arrRet['error_no']) {
            Order_BusinessError::throwException(Order_Error_Code::RAL_ERROR);
        }
        return $arrRet;
    }


    /**
     * 获取入库单列表（分页）
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
        // 订单号ID获取，分解为参数类型及单号[source_order_id, source_order_type]
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
     * 查询入库单详情
     * @param $strStockinOrderId
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getStockinOrderInfoByStockinOrderId($strStockinOrderId)
    {
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));

        if (empty($intStockinOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        return Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($intStockinOrderId);
    }

    /**
     * 查询入库单商品列表（分页）
     * @param $strStockinOrderId
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinOrderSkuList($strStockinOrderId, $intPageNum, $intPageSize)
    {
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));

        if (empty($intStockinOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        return Model_Orm_StockinOrderSku::getStockinOrderSkuList($intStockinOrderId, $intPageNum, $intPageSize);
    }

    /**
     * 分解获取关联入库单的单号，只处理ASN和SOO两种订单号，否则返回null
     * 如果订单号前缀类型不在给定的数组内则抛出参数错误异常
     * [source_order_id, source_order_type]
     * @param $strSourceOrderId
     * @param $arrStockinOrderType
     * @return null|array[source_order_id, source_order_type]
     * @throws Order_Error
     */
    private function getSourceOrderId($strSourceOrderId, $arrStockinOrderType)
    {
        $arrSourceOrderIdInfo = [];
        if (empty($strSourceOrderId)) {
            return $arrSourceOrderIdInfo;
        }

        // preg_match('/^ASN\d{13}$/', $strSourceOrderId)
        if (!empty(preg_match('/^' . Nscm_Define_OrderPrefix::ASN . '\d{13}$/', $strSourceOrderId))) {
            if (false === Order_Util::valueIsInArray(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE, $arrStockinOrderType)) {
                Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimReserveOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        // preg_match('/^SOO\d{13}$/', $strSourceOrderId)
        if (!empty(preg_match('/^' . Nscm_Define_OrderPrefix::SOO . '\d{13}$/', $strSourceOrderId))) {
            if (false === Order_Util::valueIsInArray(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT, $arrStockinOrderType)) {
                Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimStockoutOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        return $arrSourceOrderIdInfo;
    }
}
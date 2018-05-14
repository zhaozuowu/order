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
    private function formatStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo, $intOrderType = 1)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intOrderType) {
            $intPlanAmount =  $sourceOrderSkuInfo['reserve_order_sku_plan_amount'];
        } else {
            // 销退入库，计划入库数等于出库单拣货数
            $intPlanAmount = $sourceOrderSkuInfo['pickup_amount'];
        }
        $intSkuPrice = $sourceOrderSkuInfo['sku_price'];
        $intSkuPriceTax = $sourceOrderSkuInfo['sku_price_tax'];
        $arrDbStockinOrderSkuExtraInfo = [];
        // amount
        $intTotalAmount = 0;
        $intSkuGoodAmount = 0;
        $intSkuDefectiveAmount = 0;
        $i = 0;
        foreach ($arrSkuInfo['real_stockin_info'] as $arrRealStockinInfo) {
            if (0 == $arrRealStockinInfo['amount'] && count($arrSkuInfo['real_stockin_info']) > 1) {
                Order_BusinessError::throwException(Order_Error_Code::SKU_AMOUNT_CANNOT_EMPTY);
            }
            $intSkuAmount = intval($arrRealStockinInfo['amount']);
            if(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intOrderType){
                // 销退入库校验良品状态
                $intSkuGoodAmount = intval($arrRealStockinInfo['sku_good_amount']);
                $intSkuDefectiveAmount = intval($arrRealStockinInfo['sku_defective_amount']);
            } else {
                // 预约入库则认为全为良品
                $intSkuGoodAmount = $intSkuAmount;
                $intSkuDefectiveAmount = 0;
            }
            $arrDbStockinOrderSkuExtraInfo[] = [
                'amount' => $intSkuAmount,
                'expire_date' => $arrRealStockinInfo['expire_date'],
                'sku_good_amount' => $intSkuGoodAmount,
                'sku_defective_amount' => $intSkuDefectiveAmount,
            ];

            if($intSkuAmount != ($intSkuGoodAmount + $intSkuDefectiveAmount)){
                // 校验良品非良品合法性
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKIN_SKU_AMOUNT_DEFECTS_NOT_MATCH);
            }
            $i++;
            $intTotalAmount += intval($arrRealStockinInfo['amount']);
            if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intOrderType
                && $i > Order_Define_StockinOrder::STOCKIN_SKU_EXP_DATE_MAX) {
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
            'sku_tax_rate' => $sourceOrderSkuInfo['sku_tax_rate'],
            'sku_effect_type' => $sourceOrderSkuInfo['sku_effect_type'],
            'sku_effect_day' => $sourceOrderSkuInfo['sku_effect_day'],
            'stockin_order_sku_total_price' => $intTotalAmount * $intSkuPrice,
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $intSkuPriceTax,
            'stockout_order_sku_amount' => $intPlanAmount,  // 预约入库单 出库数 等于 计划入库数，销退入库单 出库数 等于 拣货数
            'reserve_order_sku_plan_amount' => $intPlanAmount,
            'stockin_order_sku_real_amount' => $intTotalAmount,
            'stockin_order_sku_extra_info' => json_encode($arrDbStockinOrderSkuExtraInfo),
        ];
    }

    /**
     * get stock price
     * @param int $intWarehouseId
     * @param int[] $arrSkuIds
     * @return array
     */
    private function getStockPrice($intWarehouseId, $arrSkuIds)
    {
        $daoStock = new Dao_Ral_Stock();
        $arrRet = $daoStock->getStockInfo($intWarehouseId, $arrSkuIds);
        $arrRes = [];
        foreach ($arrRet as $row) {
            $arrRes[$row['sku_id']] = [
                'sku_price' => $row['cost_unit_price'],
                'sku_price_tax' => $row['cost_unit_price_tax'],
            ];
        }
        return $arrRes;
    }

    /**
     * assemble price
     * @param int $intWarehouseId
     * @param array[] $arrSkus
     * @param int $intType
     * @return array
     * @throws Order_BusinessError
     */
    private function assemblePrice($intWarehouseId, $arrSkus, $intType)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intType) {
            $arrSkuIds = array_column($arrSkus, 'sku_id');
            $arrStockPrice = $this->getStockPrice($intWarehouseId, $arrSkuIds);
            if (count($arrSkuIds) != count($arrStockPrice)) {
                $strTip = '获取sku价格失败！相关sku：' . implode(',', array_diff($arrSkuIds, array_keys($arrStockPrice)));
                Order_BusinessError::throwException(Order_Error_Code::RAL_ERROR, $strTip);
            }
            foreach ($arrSkus as $key => $row) {
                $arrSkus[$key] = array_merge($row, $arrStockPrice[$row['sku_id']] ?? []);
            }
        }
        return $arrSkus;
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
     * calculate total sku plan amount
     * @param array $arrDbSkus
     * @return int
     */
    private function calculateTotalSkuPlanAmount($arrDbSkus)
    {
        $intResult = 0;
        foreach ($arrDbSkus as $arrSku) {
            $intResult += intval($arrSku['reserve_order_sku_plan_amount']);
        }
        return $intResult;
    }

    /**
     * get db stock in skus
     * @param int $intStockinOrderId
     * @param array $arrReserveOrderSkus
     * @param array $arrSkuInfoList
     * @param int $intType
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    private function getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList, $intType)
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
            $arrSkuRow = $this->formatStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo, $intType);
            if (0 == $arrSkuRow['stockin_order_sku_real_amount']
                && Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intType) {
                Order_BusinessError::throwException(Order_Error_Code::SKU_AMOUNT_CANNOT_EMPTY);
            }
            $arrDbSkuInfoList[$arrSkuInfo['sku_id']] = $arrSkuRow;
            unset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']]);
        }
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intType && !empty($arrHashReserveOrderSkus)) {
            Order_BusinessError::throwException(Order_Error_Code::ALL_SKU_MUST_STOCKIN);
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
        $arrSourceOrderSkus = $this->assemblePrice($intWarehouseId, $arrSourceOrderSkus, $intType);
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrSourceOrderSkus, $arrSkuInfoList, $intType);
        $intStockinOrderRealAmount = $this->calculateTotalSkuAmount($arrDbSkuInfoList);
        if (empty($intStockinOrderRealAmount)) {
            Order_BusinessError::throwException(Order_Error_Code::TOTAL_COUNT_CANNOT_EMPTY);
        }
        $intStockinOrderTotalPrice = $this->calculateTotalPrice($arrDbSkuInfoList);
        $intStockinOrderTotalPriceTax = $this->calculateTotalPriceTax($arrDbSkuInfoList);
        $intStockinOrderType = intval($intType);
        // 目前预约单无客户id
        $intCustomerId = 0;
        // 目前预约单无客户名称
        $strCustomerName = '';
        // 目前手动创建的入库单（预约单入库/手动销退入库类型 - 设置系统类型为手动销退入库）
        $intStockInOrderDataSourceType = Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_MANUAL_CREATE;
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockinOrderType) {
            // 预约入库单无业态类型，设为0
            $intStockinOrderSource = 0;
            $intSourceOrderId = intval($arrSourceOrderInfo['reserve_order_id']);
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['reserve_order_plan_amount'];
            $strSourceSupplierId = strval($arrSourceOrderInfo['vendor_id']);
            $intReserveOrderPlanTime = $arrSourceOrderInfo['reserve_order_plan_time'];
        } else {
            // 销退入库业态类型等于出库单业态类型
            $intStockinOrderSource = intval($arrSourceOrderInfo['stockout_order_source']);
            $intSourceOrderId = intval($arrSourceOrderInfo['stockout_order_id']);
            // 销退入库单的计划入库数等于出库单拣货数
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['stockout_order_pickup_amount'];
            $strSourceSupplierId = strval($arrSourceOrderInfo['customer_id']);
            $intCustomerId = $arrSourceOrderInfo['customer_id'];
            $strCustomerName = $arrSourceOrderInfo['customer_name'];
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
            $intStockInOrderDataSourceType, $intStockinOrderSource, $intSourceOrderId, $strSourceSupplierId, $strSourceInfo, $intStockinOrderStatus,
            $intWarehouseId, $strWarehouseName, $intCityId, $strCityName, $intStockinTime, $intReserveOrderPlanTime,
            $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
            $strStockinOrderRemark, $arrDbSkuInfoList, $intStockinOrderTotalPrice, $intStockinOrderTotalPriceTax,
            $intCustomerId,$strCustomerName) {
            $intVendorId = $intStockinOrderType == Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE ?
                intval($strSourceSupplierId) : 0;
            $arrStock = $this->notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $intVendorId, $arrDbSkuInfoList);
            $intStockinBatchId = $arrStock['stockin_batch_id'];
            Model_Orm_StockinOrder::createStockinOrder(
                $intStockinOrderId,
                $intStockinOrderType,
                $intStockInOrderDataSourceType,
                $intStockinOrderSource,
                $intSourceOrderId,
                $intStockinBatchId,
                $strSourceSupplierId,
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
                $intStockinOrderTotalPriceTax,
                $intCustomerId,
                $strCustomerName);
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
                'upc' => strval($arrSku['upc_id']),
                'real_amount' => strval($arrSku['stockin_order_sku_real_amount']),
                'unit' => strval(Order_Define_Sku::UPC_UNIT_MAP[$arrSku['upc_unit']]),
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
        Dao_Ral_SyncInbound::syncInboundSelf($intReserveOrderId, Order_Define_StockinOrder::NSCM_SURE_STOCKIN,
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
     * call stock
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intWarehouseId
     * @param int $intVendorId
     * @param array $arrDbSkuInfoList
     * @return int
     * @throws Nscm_Exception_Business
     * @throws Nscm_Exception_System
     */
    public function notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $intVendorId, $arrDbSkuInfoList)
    {
        $arrStockinSkuInfo = [];
        foreach ($arrDbSkuInfoList as $row) {
            $arrBatchInfo = $this->calculateExpire($row);
            if (!empty($arrBatchInfo)) {
                $arrStockinSkuInfo[] = [
                    'sku_id'        => $row['sku_id'],
                    'unit_price'    => $row['sku_price'],
                    'unit_price_tax'=> $row['sku_price_tax'],
                    'batch_info'    => $arrBatchInfo,
                ];
            }
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
        return $arrRet;
    }


    /**
     * 获取入库单列表（分页）
     * @param $strStockinOrderType
     * @param $intDataSource
     * @param $strStockinOrderId,
     * @param $intStockinOrderSourceType
     * @param $intStockinOrderStatus
     * @param $strWarehouseId
     * @param $strSourceSupplierId
     * @param $strSourceOrderId
     * @param $strCustomerName
     * @param $strCustomerId
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $arrStockinDestroyTime
     * @param $intPrintStatus
     * @param $intPageNum
     * @param $intPageSize
     * @return mixed
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function getStockinOrderList(
        $strStockinOrderType,
        $intDataSource,
        $strStockinOrderId,
        $intStockinOrderSourceType,
        $intStockinOrderStatus,
        $strWarehouseId,
        $strSourceSupplierId,
        $strCustomerName,
        $strCustomerId,
        $strSourceOrderId,
        $arrCreateTime,
        $arrOrderPlanTime,
        $arrStockinTime,
        $arrStockinDestroyTime,
        $intPrintStatus,
        $intPageNum,
        $intPageSize)
    {
        $arrStockinOrderType = Order_Util::extractIntArray($strStockinOrderType);
        // 校验入库单类型参数是否合法
        if (false === Model_Orm_StockinOrder::isStockinOrderTypeCorrect($arrStockinOrderType)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        $intStockinOrderId = Order_Util::trimStockinOrderIdPrefix($strStockinOrderId);
        if(empty($strWarehouseId)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        // 如果填写则校验参数类型
        if (!empty($intStockinOrderSourceType)
            && !isset(Nscm_Define_NWmsStockInOrder::STOCKIN_ORDER_SOURCE_DEFINE[$intStockinOrderSourceType])) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        // 如果填写则校验参数类型
        if (!empty($intStockinOrderStatus)
            && !isset(Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_DEFINE[$intStockinOrderStatus])) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
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

        $arrStockinDestroyTime['start'] = intval($arrStockinDestroyTime['start']);
        $arrStockinDestroyTime['end'] = intval($arrStockinDestroyTime['end']);

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

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrStockinDestroyTime['start'],
                $arrStockinDestroyTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        return Model_Orm_StockinOrder::getStockinOrderList(
            $arrStockinOrderType,
            $intDataSource,
            $intStockinOrderId,
            $intStockinOrderSourceType,
            $intStockinOrderStatus,
            $arrWarehouseId,
            $strSourceSupplierId,
            $strCustomerName,
            $strCustomerId,
            $arrSourceOrderIdInfo,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $arrStockinDestroyTime,
            $intPrintStatus,
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
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
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
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        return Model_Orm_StockinOrderSku::getStockinOrderSkuList($intStockinOrderId, $intPageNum, $intPageSize);
    }

    /**
     * 查询入库单商品列表（不分页）
     * @param $strStockinOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinOrderSkus($strStockinOrderId)
    {
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));
        if (empty($intStockinOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        return Model_Orm_StockinOrderSku::getStockinOrderSkus($intStockinOrderId);
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
                Order_Error::throwException(Order_Error_Code::PARAM_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimReserveOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        // preg_match('/^SOO\d{13}$/', $strSourceOrderId)
        if (!empty(preg_match('/^' . Nscm_Define_OrderPrefix::SOO . '\d{13}$/', $strSourceOrderId))) {
            if (false === Order_Util::valueIsInArray(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT, $arrStockinOrderType)) {
                Order_Error::throwException(Order_Error_Code::PARAM_ERROR);
            }

            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimStockoutOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        return $arrSourceOrderIdInfo;
    }


    /**
     * 获取入库单打印列表
     * @param $arrOrderIds
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinOrderPrintList($arrOrderIds)
    {
        if (empty($arrOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $ret = [];
        $arrConditions = $this->getPrintConditions($arrOrderIds);
        $arrColumns = ['stockin_order_id','stockin_order_type','source_order_id','warehouse_id','warehouse_name','stockin_order_remark','stockin_order_real_amount','source_info'];
        $arrRetList = Model_Orm_StockinOrder::findRows($arrColumns, $arrConditions);
        if (empty($arrRetList)) {
            return $ret;
        }

        $arrWarehouseIds = array_column($arrRetList,'warehouse_id');
        $objDao = new Dao_Ral_Order_Warehouse();
        $arrWarehouseList = $objDao->getWareHouseList($arrWarehouseIds);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $arrSkuColumns = ['stockin_order_id','upc_id','sku_name','sku_net','upc_unit','reserve_order_sku_plan_amount','stockin_order_sku_real_amount','sku_net_unit','stockin_order_sku_extra_info'];
        $arrReserveSkuList = Model_Orm_StockinOrderSku::findRows($arrSkuColumns, $arrConditions);
        $arrReserveSkuList = $this->arrayToKeyValue($arrReserveSkuList, 'stockin_order_id');
        foreach ($arrRetList as $key=>$item) {
            $arrSourceInfo =empty($item['source_info']) ? []:json_decode($item['source_info'],true);
            $arrRetList[$key]['vendor_id'] = isset($arrSourceInfo['vendor_id']) ? $arrSourceInfo['vendor_id']:0;
            $arrRetList[$key]['vendor_name'] = isset($arrSourceInfo['vendor_name']) ? $arrSourceInfo['vendor_name']:'';
            $arrRetList[$key]['customer_id'] = isset($arrSourceInfo['customer_id']) ? $arrSourceInfo['customer_id']:0;
            $arrRetList[$key]['customer_name'] = isset($arrSourceInfo['customer_name']) ? $arrSourceInfo['customer_name']:'';
            $arrRetList[$key]['warehouse_name'] = empty($item['warehouse_name']) ?(isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['warehouse_name']:''):$item['warehouse_name'];
            $arrRetList[$key]['warehouse_contact'] = isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['contact']:'';
            $arrRetList[$key]['warehouse_contact_phone'] = isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['contact_phone']:'';
            $arrRetList[$key]['skus'] = isset($arrReserveSkuList[$item['stockin_order_id']]) ? $arrReserveSkuList[$item['stockin_order_id']]:[];
            if(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE === $item['stockin_order_type']){
                $arrRetList[$key]['source_order_id'] = empty($item['source_order_id']) ? '' : Nscm_Define_OrderPrefix::ASN . intval($item['source_order_id']);
            }else if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT === $item['stockin_order_type']){
                $arrRetList[$key]['source_order_id'] = empty($item['source_order_id']) ? '' : Nscm_Define_OrderPrefix::SOO . intval($item['source_order_id']);
            }
        }
        return $arrRetList;

    }

    /**
     * 获取预约入库单打印条件
     * @param $arrOrderIds
     * @return array
     */
    private function getPrintConditions($arrOrderIds)
    {

        $arrOrderIds = $this->batchTrimStockinOrderIdPrefix($arrOrderIds);
        // 只查询未软删除的
        $arrConditions = [
            'stockin_order_id' => ['in', $arrOrderIds],
            'is_delete'  => Order_Define_Const::NOT_DELETE,
        ];
        return $arrConditions;
    }

    /**
     * 批次去除预约单开头的ASN开头部分内容
     * @param $arrOrderIds
     */
    private function batchTrimStockinOrderIdPrefix($arrStockinOrderIds)
    {
        foreach ($arrStockinOrderIds as $intKey => $strStockinOrderId) {
            $strStockinOrderId = Order_Util::trimStockinOrderIdPrefix($strStockinOrderId);
            $strStockinOrderId = Order_Util::trimReserveOrderIdPrefix($strStockinOrderId);
            $arrStockinOrderIds[$intKey] = intval($strStockinOrderId);
        }
        return $arrStockinOrderIds;
    }

    /**
     * transfer array to key value pair
     * @param array $arr
     * @param string $primary_key
     * @return array
     */
    private function arrayToKeyValue($arr, $primary_key)
    {
        if (empty($arr) || empty($primary_key)) {
            return array();
        }
        $arrKeyValue = array();
        foreach ($arr as $key=>$item) {
            if (isset($item[$primary_key])) {
                $arrKeyValue[$item[$primary_key]][] = $item;
            }
        }
        return $arrKeyValue;
    }

    /**
     *创建系统销退入库单（货架撤点）
     * @param $arrInput
     * @return int
     * @throws Nscm_Exception_Error
     * @throws Order_Error
     */
    public function createWithdrawStockInOrder($arrInput)
    {
        $intShipmentOrderId = $arrInput['shipment_order_id'];
        $intStockInOrderSource = $arrInput['stockin_order_source'];
        $intWarehouseId = $arrInput['warehouse_id'];
        $arrRequestSkuInfoList = $arrInput['sku_info_list'];
        $intStockInOrderId = $arrInput['intStockInOrderId'];
        $assetInformation = $arrInput['asset_information'];
        $arrSkuIds = array_column($arrRequestSkuInfoList, 'sku_id');
        $arrRequestSkuInfoMap = [];
        foreach ($arrRequestSkuInfoList as $arrRequestSkuInfo) {
            $arrRequestSkuInfoMap[$arrRequestSkuInfo['sku_id']] = $arrRequestSkuInfo['sku_amount'];
        }
        $arrSkuInfoList = $this->getSkuInfoList($arrSkuIds);
        $arrSkuPriceList = $this->getSkuPrice($arrSkuIds, $intWarehouseId, $arrSkuInfoList);
        $arrDbSkuInfoList = $this->assembleWithdrawDbSkuList($arrRequestSkuInfoMap, $arrSkuInfoList,
            $arrSkuPriceList);
        $intOrderReturnReason = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REMOVE_SITE;
        $strOrderReturnReasonText = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[$intOrderReturnReason];
        $intStockinOrderPlanAmount = $this->calculateTotalSkuPlanAmount($arrDbSkuInfoList);
        $intStockinOrderTotalPrice = $this->calculateTotalPrice($arrDbSkuInfoList);
        $intStockinOrderTotalPriceTax = $this->calculateTotalPriceTax($arrDbSkuInfoList);
        $arrSourceInfo = $arrInput['customer_info'];
        $strSourceSupplierId = $strCustomerId = $arrSourceInfo['customer_id'];
        $strCustomerName = $arrSourceInfo['customer_name'];
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_WAITING;
        $arrWarehouseInfo = $this->getWarehouseInfoById($intWarehouseId);
        if (empty($arrWarehouseInfo)) {
            Order_Error::throwException(Order_Error_Code::RAL_ERROR);
        }
        $strWarehouseName = $arrWarehouseInfo['warehouse_name'];
        $intCityId = $arrWarehouseInfo['city']['id'];
        $strCityName = $arrWarehouseInfo['city']['name'];
        $intStockInOrderCreatorId = 0;
        $strStockInOrderCreatorName = 'System';
        $intStockInOrderType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
        $intStockInOrderDataSourceType = Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM;
        $strStockInOrderRemark = empty($arrInput['stockin_order_remark']) ? '':strval($arrInput['stockin_order_remark']);
        if (empty($arrDbSkuInfoList) || empty($intStockinOrderPlanAmount)) {
            return 0;
        }
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($intStockInOrderId,$intStockInOrderType,
            $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $intOrderReturnReason, $intStockInOrderDataSourceType,
            $strWarehouseName, $intCityId, $strCityName,$intShipmentOrderId, $strCustomerName, $strCustomerId, $intStockInOrderSource,
            $intStockinOrderPlanAmount, $intStockInOrderCreatorId, $strStockInOrderCreatorName, $strOrderReturnReasonText,
            $strSourceSupplierId, $strStockInOrderRemark, $arrDbSkuInfoList, $intStockinOrderTotalPrice, $intStockinOrderTotalPriceTax,$assetInformation) {
            Model_Orm_StockinOrder::createRemoveSiteStockInOrder(
                $intStockInOrderId,
                $intStockInOrderType,
                $intStockInOrderDataSourceType,
                $intStockInOrderSource,
                $intOrderReturnReason,
                $strOrderReturnReasonText,
                $strSourceInfo,
                $intStockinOrderStatus,
                $intCityId,
                $strCityName,
                $intWarehouseId,
                $strWarehouseName,
                $intStockinOrderPlanAmount,
                $intStockInOrderCreatorId,
                $strStockInOrderCreatorName,
                $strStockInOrderRemark,
                $intStockinOrderTotalPrice,
                $intStockinOrderTotalPriceTax,
                $intShipmentOrderId,
                $strCustomerId,
                $strCustomerName,
                $strSourceSupplierId,$assetInformation);
            Model_Orm_StockinOrderSku::batchCreateStockinOrderSku($arrDbSkuInfoList, $intStockInOrderId);
        });
        return $intStockInOrderId;

    }
    /**
     * 创建系统销退入库单
     * @
     */
    /**
     * @param  integer $intStockInOrderId
     * @param  array   $arrSourceOrderSkuList
     * @param  array   $arrSourceOrderInfo
     * @param  array   $arrRequestSkuInfoList
     * @param  int     $intShipmentOrderId
     * @param  string  $strStockInOrderRemark
     * @param  integer $intStockInOrderSource
     * @return integer $intStockInOrderId
     * @throws Order_Error
     * @throws Exception
     */
    public function createSysStockInOrder($intStockInOrderId, $arrSourceOrderSkuList, $arrSourceOrderInfo, $intShipmentOrderId,
                                          $arrRequestSkuInfoList, $strStockInOrderRemark, $intStockInOrderSource)
    {
        if (empty($intShipmentOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($arrSourceOrderSkuList)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($arrSourceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($arrRequestSkuInfoList)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR, 'sku list is invalided');
        }
        if (empty($intStockInOrderSource)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR, 'stock in order source is invalided');
        }
        $intWarehouseId = $arrSourceOrderInfo['warehouse_id'];
        $arrSkuIds = array_column($arrRequestSkuInfoList, 'sku_id');
        $arrRequestSkuInfoMap = [];
        foreach ($arrRequestSkuInfoList as $arrRequestSkuInfo) {
            $arrRequestSkuInfoMap[$arrRequestSkuInfo['sku_id']] = $arrRequestSkuInfo['sku_amount'];
        }
        $arrSkuInfoList = $this->getSkuInfoList($arrSkuIds);
        $arrSkuPriceList = $this->getSkuPrice($arrSkuIds, $intWarehouseId, $arrSkuInfoList);
        $arrDbSkuInfoList = $this->assembleDbSkuList($arrSourceOrderSkuList, $arrRequestSkuInfoMap, $arrSkuInfoList,
                    $arrSkuPriceList);
        list($intOrderReturnReason, $strOrderReturnReasonText) = $this->getOrderReturnReason($arrSourceOrderSkuList, $arrRequestSkuInfoMap);

        $intStockinOrderPlanAmount = $this->calculateTotalSkuPlanAmount($arrDbSkuInfoList);
        $intStockinOrderTotalPrice = $this->calculateTotalPrice($arrDbSkuInfoList);
        $intStockinOrderTotalPriceTax = $this->calculateTotalPriceTax($arrDbSkuInfoList);
        $intSourceOrderId = intval($arrSourceOrderInfo['stockout_order_id']);
        $arrSourceInfo = $this->getSourceInfo($arrSourceOrderInfo, Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT);
        $strSourceSupplierId = $strCustomerId = $arrSourceOrderInfo['customer_id'];
        $strCustomerName = $arrSourceOrderInfo['customer_name'];
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_WAITING;
        $arrWarehouseInfo = $this->getWarehouseInfoById($intWarehouseId);
        if (empty($arrWarehouseInfo)) {
            Order_Error::throwException(Order_Error_Code::RAL_ERROR);
        }
        $strWarehouseName = $arrWarehouseInfo['warehouse_name'];
        $intCityId = $arrWarehouseInfo['city']['id'];
        $strCityName = $arrWarehouseInfo['city']['name'];
        $intStockInOrderCreatorId = 0;
        $strStockInOrderCreatorName = 'System';
        $intStockInOrderType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
        $intStockInOrderDataSourceType = Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM;
        $strStockInOrderRemark = strval($strStockInOrderRemark);
        if (empty($arrDbSkuInfoList) || empty($intStockinOrderPlanAmount)) {
            return 0;
        }
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($intStockInOrderId, $intStockInOrderType,
            $intSourceOrderId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $intOrderReturnReason, $intStockInOrderDataSourceType,
            $strWarehouseName, $intCityId, $strCityName,$intShipmentOrderId, $strCustomerName, $strCustomerId, $intStockInOrderSource,
            $intStockinOrderPlanAmount, $intStockInOrderCreatorId, $strStockInOrderCreatorName, $strOrderReturnReasonText,
            $strSourceSupplierId, $strStockInOrderRemark, $arrDbSkuInfoList, $intStockinOrderTotalPrice, $intStockinOrderTotalPriceTax) {
            Model_Orm_StockinOrder::createStayStockInOrder(
                $intStockInOrderId,
                $intStockInOrderType,
                $intStockInOrderDataSourceType,
                $intStockInOrderSource,
                $intSourceOrderId,
                $intOrderReturnReason,
                $strOrderReturnReasonText,
                $strSourceInfo,
                $intStockinOrderStatus,
                $intCityId,
                $strCityName,
                $intWarehouseId,
                $strWarehouseName,
                $intStockinOrderPlanAmount,
                $intStockInOrderCreatorId,
                $strStockInOrderCreatorName,
                $strStockInOrderRemark,
                $intStockinOrderTotalPrice,
                $intStockinOrderTotalPriceTax,
                $intShipmentOrderId,
                $strCustomerId,
                $strCustomerName,
                $strSourceSupplierId);
            Model_Orm_StockinOrderSku::batchCreateStockinOrderSku($arrDbSkuInfoList, $intStockInOrderId);
        });
        return $intStockInOrderId;
    }

    /**
     * @param  array $arrSkuIds
     * @param  int   $intWarehouseId
     * @param  array $arrSkuInfoList
     * @return array
     * @throws Nscm_Exception_Error
     */
    private function getSkuPrice($arrSkuIds, $intWarehouseId, $arrSkuInfoList)
    {
        //先从仓库获取成本价
        $arrSkuPriceInWarehouse = $this->getStockPrice($intWarehouseId, $arrSkuIds);
        $arrSkuIdsInWarehouse = array_keys($arrSkuPriceInWarehouse);
        $arrSkuIdsNotInWarehouse = array_diff($arrSkuIds, $arrSkuIdsInWarehouse);
        //仓库中无此商品，则去彩云获取最新含有此sku有效报价的价格
        if (!empty($arrSkuIdsNotInWarehouse)) {
            $arrSkuPriceInVendor = $this->getVendorSkuPrice($arrSkuIdsNotInWarehouse, $arrSkuInfoList);
            foreach ($arrSkuPriceInVendor as $intSkuId => $item) {
                $arrSkuPriceInWarehouse[$intSkuId] = $item;
            }
        }
        return $arrSkuPriceInWarehouse;
    }

    /**
     * get vendor sku price
     * @param  array $arrSkuIds
     * @param  array $arrSkuInfoList
     * @return array
     * @throws Nscm_Exception_Error
     */
    private function getVendorSkuPrice($arrSkuIds, $arrSkuInfoList)
    {
        $arrRes = [];
        if (!empty($arrSkuIds)) {
            $daoStock = new Dao_Ral_Vendor();
            $arrRet = $daoStock->getSkuPrice($arrSkuIds);
            $arrRes = [];
            foreach ($arrRet as $row) {
                $arrRes[$row['sku_id']] = [
                    'sku_price' => Nscm_Service_Price::convertFenToDefault($row['quotation_sku_price']),
                    'sku_price_tax' => Nscm_Service_Price::convertFenToDefault(
                        Nscm_Service_Price::calculateUnitPrice(
                            $row['quotation_sku_price'],
                            Order_Define_Sku::SKU_TAX_NUM[$arrSkuInfoList[$row['sku_id']]['sku_tax_rate']]
                    )),
                ];
            }
        }
        return $arrRes;
    }
    /**
     * @param  array $arrSkuIds
     * @return array
     * @throws Nscm_Exception_Error
     */
    private function getSkuInfoList($arrSkuIds)
    {
        $daoRalSku = new Dao_Ral_Sku();
        return $daoRalSku->getSkuInfos($arrSkuIds);
    }

    /**
     *
     * @param $arrRequestSkuInfoList
     * @param $arrSkuInfoList
     * @param $arrSkuPriceList
     * @return array
     */
    private function assembleWithdrawDbSkuList($arrRequestSkuInfoList, $arrSkuInfoList, $arrSkuPriceList)
    {
        $arrSourceOrderSkuMap = [];
        $arrDbSkuList = [];
        foreach ($arrSkuPriceList as $intSkuId => $arrSkuPriceInfo) {
            if (0 >= $arrRequestSkuInfoList[$intSkuId]) {
                continue;
            }
            $arrSkuInfo = $arrSkuInfoList[$intSkuId];
            $arrDbSku = [
                'sku_id' => $intSkuId,
                'upc_id' => $arrSkuInfo['min_upc']['upc_id'],
                'upc_unit' => $arrSkuInfo['min_upc']['upc_unit'],
                'upc_unit_num' => $arrSkuInfo['min_upc']['upc_unit_num'],
                'sku_name' => $arrSkuInfo['sku_name'],
                'sku_net' => $arrSkuInfo['sku_net'],
                'sku_net_unit' => $arrSkuInfo['sku_net_unit'],
                'sku_net_gram' => $arrSkuInfo['sku_weight'],
                'sku_price' => $arrSkuPriceInfo['sku_price'],
                'sku_price_tax' => $arrSkuPriceInfo['sku_price_tax'],
                'sku_tax_rate' => $arrSkuInfo['sku_tax_rate'],
                'sku_effect_type' => $arrSkuInfo['sku_effect_type'],
                'sku_effect_day' => $arrSkuInfo['sku_effect_day'],
                'reserve_order_sku_plan_amount' => $arrRequestSkuInfoList[$intSkuId],
                'stockout_order_sku_amount' => 0,
            ];
            $arrDbSku['stockin_order_sku_total_price'] = bcmul($arrRequestSkuInfoList[$intSkuId], $arrSkuPriceInfo['sku_price']);
            $arrDbSku['stockin_order_sku_total_price_tax'] = bcmul($arrRequestSkuInfoList[$intSkuId], $arrSkuPriceInfo['sku_price_tax']);
            $arrDbSku['stockin_reason'] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REMOVE_SITE;
            $arrDbSkuList[] = $arrDbSku;
        }
        return $arrDbSkuList;

    }
    /**
     * @param  array $arrSourceOrderSkuList
     * @param  array $arrRequestSkuInfoList
     * @param  array $arrSkuInfoList
     * @param  array $arrSkuPriceList
     * @return array
     */
    private function assembleDbSkuList($arrSourceOrderSkuList, $arrRequestSkuInfoList, $arrSkuInfoList,
                                       $arrSkuPriceList)
    {
        $arrSourceOrderSkuMap = [];
        foreach ($arrSourceOrderSkuList as $arrSourceOrderSku) {
            $arrSourceOrderSkuMap[$arrSourceOrderSku['sku_id']] = $arrSourceOrderSku['pickup_amount'];
        }
        $arrDbSkuList = [];
        foreach ($arrSkuPriceList as $intSkuId => $arrSkuPriceInfo) {
            if (0 >= $arrRequestSkuInfoList[$intSkuId]) {
                continue;
            }
            $arrSkuInfo = $arrSkuInfoList[$intSkuId];
            $arrDbSku = [
                'sku_id' => $intSkuId,
                'upc_id' => $arrSkuInfo['min_upc']['upc_id'],
                'upc_unit' => $arrSkuInfo['min_upc']['upc_unit'],
                'upc_unit_num' => $arrSkuInfo['min_upc']['upc_unit_num'],
                'sku_name' => $arrSkuInfo['sku_name'],
                'sku_net' => $arrSkuInfo['sku_net'],
                'sku_net_unit' => $arrSkuInfo['sku_net_unit'],
                'sku_net_gram' => $arrSkuInfo['sku_weight'],
                'sku_price' => $arrSkuPriceInfo['sku_price'],
                'sku_price_tax' => $arrSkuPriceInfo['sku_price_tax'],
                'sku_tax_rate' => $arrSkuInfo['sku_tax_rate'],
                'sku_effect_type' => $arrSkuInfo['sku_effect_type'],
                'sku_effect_day' => $arrSkuInfo['sku_effect_day'],
                'reserve_order_sku_plan_amount' => $arrRequestSkuInfoList[$intSkuId],
                'stockout_order_sku_amount' => 0,
            ];
            $arrDbSku['stockin_order_sku_total_price'] = bcmul($arrRequestSkuInfoList[$intSkuId], $arrSkuPriceInfo['sku_price']);
            $arrDbSku['stockin_order_sku_total_price_tax'] = bcmul($arrRequestSkuInfoList[$intSkuId], $arrSkuPriceInfo['sku_price_tax']);
            if (isset($arrSourceOrderSkuMap[$intSkuId])) {
                $arrDbSku['stockout_order_sku_amount'] = $arrSourceOrderSkuMap[$intSkuId];
                if ($arrSourceOrderSkuMap[$intSkuId] > $arrRequestSkuInfoList[$intSkuId]) { //部分拒收
                    $arrDbSku['stockin_reason'] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT;
                } else { //全部拒收
                    $arrDbSku['stockin_reason'] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REJECT_ALL;
                }
            } else { //下架
                $arrDbSku['stockin_reason'] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_CHANGE;
            }
            $arrDbSkuList[] = $arrDbSku;
        }
        return $arrDbSkuList;
    }

    /**
     * @param  int $intWarehouseId
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_Error
     */
    private function getWarehouseInfoById($intWarehouseId)
    {
        $objDaoWarehouse = new Dao_Ral_Order_Warehouse();
        $arrWarehouseInfo = $objDaoWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        if (empty($arrWarehouseInfo)) {
            Order_Error::throwException(Order_Error_Code::RAL_ERROR);
        }
        return $arrWarehouseInfo;
    }

    /**
     * @param  string $strStockInOrderId 入库单id
     * @param  array  $arrSkuInfoList 入库sku信息
     * @param  string $strRemark 入库备注
     * @throws Exception
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function confirmStockInOrder($strStockInOrderId, $arrSkuInfoList, $strRemark)
    {
        if (empty($strStockInOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($arrSkuInfoList)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $intStockInOrderId = Order_Util::trimStockinOrderIdPrefix($strStockInOrderId);
        $arrStockInOrderInfo = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($intStockInOrderId);
        if (empty($arrStockInOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_NOT_EXISTED);
        }
        if (Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_CANCEL == $arrStockInOrderInfo['stockin_order_status']) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_STATUS_INVALID);
        }
        if (Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH == $arrStockInOrderInfo['stockin_order_status']) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_STATUS_FINISHED);
        }
        $intWarehouseId = $arrStockInOrderInfo['warehouse_id'];
        if (Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH != $arrStockInOrderInfo['stockin_order_status']) {
            $intStockInTime = time();
            $intStockInOrderRealAmount = $this->calculateStockInOrderRealAmount($arrSkuInfoList);
            $arrDbSkuInfoList = $this->assembleDbSkuInfoList($arrSkuInfoList, $intStockInOrderId);
            $arrStockInSkuList = $this->getStockInSkuList($intStockInOrderId, $arrSkuInfoList);
            Model_Orm_StockinOrder::getConnection()->transaction(function () use (
                $intStockInOrderId, $intStockInTime, $intStockInOrderRealAmount, $arrDbSkuInfoList, $strRemark,
                $intWarehouseId, $arrStockInSkuList) {
                $objRalStock = new Dao_Ral_Stock();
                $arrStock = $objRalStock->stockIn($intStockInOrderId,
                    Nscm_Define_Stock::STOCK_IN_TYPE_SALE_RETURN,
                    $intWarehouseId,
                    $arrStockInSkuList);
                Model_Orm_StockinOrder::confirmStockInOrder($intStockInOrderId, $intStockInTime,
                    $intStockInOrderRealAmount, $strRemark, $arrStock['stockin_batch_id']);
                Model_Orm_StockinOrderSku::confirmStockInOrderSkuList($arrDbSkuInfoList);
            });
            $intTable = Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT;
            $intType = Order_Statistics_Type::ACTION_CREATE;
            Dao_Ral_Statistics::syncStatistics($intTable, $intType, $intStockInOrderId);
            if (!empty($arrStockInOrderInfo['shipment_order_id'])) {
                $this->sendConfirmStockinOrderInfoToOms($intStockInOrderId, $arrStockInOrderInfo['shipment_order_id'], $arrStockInOrderInfo['stockin_order_source'], $arrSkuInfoList);
            }
        }
    }

    /**
     * 计算入库单真是入库总数
     * @param  array $arrSkuInfoList
     * @return int
     * @throws Order_Error
     */
    private function calculateStockInOrderRealAmount($arrSkuInfoList)
    {
        $intRealAmount = 0;
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            foreach ($arrSkuInfo['real_stockin_info'] as $arrRealSkuInfo) {
                $intAmount = $arrRealSkuInfo['amount'];
                $intAmountGood = $arrRealSkuInfo['sku_good_amount'];
                $intAmountDefective = $arrRealSkuInfo['sku_defective_amount'];
                if (0 != ($intAmount - $intAmountGood - $intAmountDefective)) {
                    Bd_Log::trace(sprintf("sku amount is invalid %s", json_encode($arrSkuInfoList['real_stockin_info'])));
                    Order_Error::throwException(Order_Error_Code::SYS_ERROR);
                }
                $intRealAmount += $intAmount;
            }
            if (0 == $intRealAmount) {
                Bd_Log::trace(sprintf("sku amount is invalid %s", json_encode($arrSkuInfoList)));
                Order_Error::throwException(Order_Error_Code::SYS_ERROR);
            }
        }

        return $intRealAmount;
    }

    private function assembleDbSkuInfoList($arrSkuInfoList, $intStockInOrderId)
    {
        $arrDbSkuInfoList = [];
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            $arrSkuRealInfoList = $arrSkuInfo['real_stockin_info'];
            $arrDbSkuInfo = [
                'stockin_order_id' => $intStockInOrderId,
                'sku_id' => $arrSkuInfo['sku_id'],
                'stockin_order_sku_real_amount' => array_sum(array_column($arrSkuRealInfoList, 'amount')),
                'stockin_order_sku_extra_info' => json_encode($arrSkuRealInfoList),
            ];
            $arrDbSkuInfoList[] = $arrDbSkuInfo;
        }
        return $arrDbSkuInfoList;
    }

    /*
     * @desc 更新一个或多个入库单为已打印
     * @name updateStockinOrderIsPrint
     * @param array $arrStockinOrderIds
     * @return bool
     */
    public function updateStockinOrderIsPrint($arrStockinOrderIds)
    {
        $arrStockinOrderIds = $this->batchTrimStockinOrderIdPrefix($arrStockinOrderIds);
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($arrStockinOrderIds) {
            foreach ($arrStockinOrderIds as $intOrderId) {
                $objStockin = Model_Orm_StockinOrder::findOne(['stockin_order_id' => $intOrderId]);
                if (!empty($objStockin)) {
                    $objStockin->stockin_order_is_print = Order_Define_StockinOrder::STOCKIN_ORDER_IS_PRINT;
                    $objStockin->update();
                }
            }
        });

        return true;
    }

    /**
     * @param  int $intStockOutOrderId
     * @return int
     */
    public function getStockInOrderIdByStockOutId($intStockOutOrderId)
    {
        //从redis中获取
        $objRedisRal = new Dao_Redis_StockInOrder();
        $intStockInOrderId = $objRedisRal->getValBySourceOrderId($intStockOutOrderId);
        //从DB中获取
        if (empty($intStockInOrderId)) {
            $intStockInOrderInfo = Model_Orm_StockinOrder::getStockInOrderInfoBySourceOrderId($intStockOutOrderId);
            if (!empty($intStockInOrderInfo)) {
                $intStockInOrderId = $intStockInOrderInfo['stockin_order_id'];
            }
        }
        return $intStockInOrderId;
    }

    /**
     * @param  int $intStockOutOrderId
     * @param  int $intStockInOrderId
     * @return void
     */
    public function setStockInOrderIdByStockOutId($intStockOutOrderId, $intStockInOrderId)
    {
        $objRedisRal = new Dao_Redis_StockInOrder();
        $objRedisRal->setStockInOrderId($intStockOutOrderId, $intStockInOrderId);
    }

    private function getOrderReturnReason($arrSourceOrderSkuList, $arrRequestSkuInfoList)
    {
        $arrSourceOrderSkuMap = [];
        foreach ($arrSourceOrderSkuList as $arrSourceOrderSku) {
            $arrSourceOrderSkuMap[$arrSourceOrderSku['sku_id']] = $arrSourceOrderSku['pickup_amount'];
        }
        $arrOrderRejectedReason = [];
        $arrOrderReturnReason = [];
        $arrOrderReturnReasonText = [];
        $boolIsOffShelf = false;
        foreach ($arrRequestSkuInfoList as $intSkuId => $intSkuAmount) {
            if (isset($arrSourceOrderSkuMap[$intSkuId])) {
                if ($arrSourceOrderSkuMap[$intSkuId] > $arrRequestSkuInfoList[$intSkuId]) { //部分拒收
                    $arrOrderRejectedReason[Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT] = 0;
                } else { //全部拒收
                    $arrOrderRejectedReason[Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REJECT_ALL] = 0;
                }
            } else {
                $boolIsOffShelf = true;
            }
        }
        if (!empty($arrOrderRejectedReason)) {
            if (1 == count($arrOrderRejectedReason)
                && key_exists(Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REJECT_ALL, $arrOrderRejectedReason)) {
                $intReturnReason = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REJECT_ALL;
            } else {
                $intReturnReason = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT;
            }
            $arrOrderReturnReason[] = $intReturnReason;
            $arrOrderReturnReasonText[] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[$intReturnReason];
        }
        if ($boolIsOffShelf) {
            $intReturnReason = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_CHANGE;
            $arrOrderReturnReason[] = $intReturnReason;
            $arrOrderReturnReasonText[] = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[$intReturnReason];
        }
        $intOrderReturnReason = array_sum($arrOrderReturnReason);
        $strOrderReturnReasonText = implode(',', $arrOrderReturnReasonText);

        return [
            $intOrderReturnReason,
            $strOrderReturnReasonText,
        ];
    }

    /**
     * 拼接入库库存时sku信息
     * @param  int  $intStockInOrderId
     * @param  array $arrSkuInfoListRequest
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    private function getStockInSkuList($intStockInOrderId, $arrSkuInfoListRequest)
    {
        $arrStockInSkuList = [];
        $arrDbStockInSkuList = $this->getStockinOrderSkuList($intStockInOrderId, 1, 0);
        $arrDbStockInSkuMap = [];
        foreach ($arrDbStockInSkuList['list'] as $arrDbStockInSkuInfo) {
            $arrDbStockInSkuMap[$arrDbStockInSkuInfo['sku_id']] = array_merge($arrDbStockInSkuInfo, [
                'unit_price' => Nscm_Service_Price::convertDefaultToFen($arrDbStockInSkuInfo['sku_price']),
                'unit_price_tax' => Nscm_Service_Price::convertDefaultToFen($arrDbStockInSkuInfo['sku_price_tax']),
            ]);
        }

        foreach ($arrSkuInfoListRequest as $arrSkuInfo) {
            if (!isset($arrDbStockInSkuMap[$arrSkuInfo['sku_id']])) {
                Order_Error::throwException(Order_Error_Code::SYS_ERROR);
            }
            $arrRealSkuInfo = $arrSkuInfo['real_stockin_info'];
            $arrStockInSkuListItem = [
                'sku_id' => $arrSkuInfo['sku_id'],
                'unit_price' => $arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['unit_price'],
                'unit_price_tax' => $arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['unit_price_tax'],
            ];
            $intSkuEffectType = $arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['sku_effect_type'];
            $intSkuEffectDate = intval($arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['sku_effect_day']);
            foreach ($arrRealSkuInfo as $arrSkuInfoItem) {
                if (Order_Define_Sku::SKU_EFFECT_TYPE_PRODUCT == $intSkuEffectType) {
                    $intProductionTime = strtotime(date('Ymd', $arrSkuInfoItem['expire_date']));
                    $intExpireTime = $intProductionTime + $intSkuEffectDate * 86400 - 1;

                } else {
                    $intExpireTime = strtotime(date('Ymd', $arrSkuInfoItem['expire_date'])) + 86399;
                    $intProductionTime = 0;
                }
                if (0 != $arrSkuInfoItem['sku_good_amount']) {
                    $arrStockInSkuListItem['batch_info'][] = [
                        'expire_time' => $intExpireTime,
                        'production_time' => $intProductionTime,
                        'amount' => $arrSkuInfoItem['sku_good_amount'],
                        'is_defective' => Nscm_Define_Stock::QUALITY_GOOD,
                    ];
                }
                if (0 != $arrSkuInfoItem['sku_defective_amount']) {
                    $arrStockInSkuListItem['batch_info'][] = [
                        'expire_time' => $intExpireTime,
                        'production_time' => $intProductionTime,
                        'amount' => $arrSkuInfoItem['sku_defective_amount'],
                        'is_defective' => Nscm_Define_Stock::QUALITY_DEFECTIVE,
                    ];
                }
            }
            $arrStockInSkuList[] = $arrStockInSkuListItem;
        }
        return $arrStockInSkuList;
    }

    /**
     * 销退入库单确认结果发送wmq
     * @param $intStockInOrderId
     * @param $arrSkuInfoList
     * @param $strRemark
     */
    public function sendConfirmStockinOrderInfoToOms($intStockInOrderId, $intShipmentOrderId, $intBizType, $arrSkuInfoList)
    {
        //$arrSkuIds = array_column($arrSkuInfoList, 'sku_id');
        //$arrConds = [
        //    'stockin_order_id' => $intStockInOrderId,
        //    'is_delete'        => Order_Define_Const::NOT_DELETE,
        //];
        //$arrFields = ['stockin_order_id', 'sku_id', 'sku_net', 'upc_unit', 'sku_name'];
        //$arrSku = Model_Orm_StockinOrderSku::findRows($arrFields, $arrConds);
        //$arrSkuDict = Order_Util_Util::arrayToKeyValue($arrSku, 'sku_id');
        $strCmd = Order_Define_Cmd::CMD_NOTIFY_OMS_CONFIRM_STOCKIN_ORDER;
        $arrSkuInfoNew = [];
        foreach ((array)$arrSkuInfoList as $arrSkuInfo){
            $arrSkuInfoNew[] = [
                'sku_id' => $arrSkuInfo['sku_id'],
                'sku_amount' => array_sum(array_column($arrSkuInfo['real_stockin_info'], 'amount')),
            ];
        }
        $arrParam = [
            'stockin_order_id' => $intStockInOrderId,
            'shipment_order_id' => $intShipmentOrderId,
            'biz_type'          => $intBizType,
            'sku_info_list'    => json_encode($arrSkuInfoNew),
        ];
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrParam, strval($intStockInOrderId));
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
        }
        return true;
    }

    /**
     * 异步通知OMS销退入库单确认结果
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function asynchronousNotifyOmsConfirmStockinResult($arrInput)
    {
        $objWrpcOms = new Dao_Wrpc_Oms();
        return $objWrpcOms->confirmStockinOrderToOms($arrInput);
    }
}
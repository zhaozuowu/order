<?php
/**
 * @name Service_Data_Stockin_StockinOrder
 * @desc Service_Data_Stockin_StockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Stockin_StockinOrder
{
    /**
     * get stockin waiting sku
     * @param $intWarehouseId
     * @return array[]
     * @throws Order_BusinessError
     */
    public function getStockinWaitingSku($intWarehouseId)
    {
        $intWarehouseId = intval($intWarehouseId);
        Bd_Log::debug('search warehouse: ' . $intWarehouseId);
        $arrOrderInfos = Model_Orm_StockinOrder::getStockinOrderList([Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT],
            Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM, 0, 0,
            Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_WAIT,  [$intWarehouseId], null, null, null, null,
            ['start' => -1, 'end' => time()], null, null, null, null, null, null);
        $arrOrderIds = array_column($arrOrderInfos['list'], 'stockin_order_id');
        Bd_Log::debug('order ids: ' . json_encode($arrOrderIds));
        if (empty($arrOrderIds)) {
            return[];
        }
        $arrSkuInfos = Model_Orm_StockinOrderSku::getBatchStockinOrderSkuList($arrOrderIds);
        return $arrSkuInfos;
    }
    /**
     * calculate stock in order sku info
     * @param int $intStockinOrderId
     * @param array $sourceOrderSkuInfo
     * @param array $arrSkuInfo
     * @param int $intOrderType
     * @throws Order_BusinessError
     * @return array
     */
    private function formatStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo, $intOrderType = 1)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intOrderType) {
            $intPlanAmount =  $sourceOrderSkuInfo['reserve_order_sku_plan_amount'];
            $intSkuFromCountry = $sourceOrderSkuInfo['sku_from_country'];
            $intUpcMinUnit = $sourceOrderSkuInfo['upc_min_unit'];
        } else {
            // 销退入库，计划入库数等于出库单拣货数
            $intPlanAmount = $sourceOrderSkuInfo['pickup_amount'];
            $intSkuFromCountry = $sourceOrderSkuInfo['import'];
            $intUpcMinUnit = $sourceOrderSkuInfo['upc_unit'];
        }
        $intSkuPrice = $sourceOrderSkuInfo['sku_price'];
        $intSkuPriceTax = $sourceOrderSkuInfo['sku_price_tax'];
        $arrDbStockinOrderSkuExtraInfo = [];
        // amount
        $intTotalAmount = 0;
        $intSkuGoodAmount = 0;
        $intSkuDefectiveAmount = 0;
        $i = 0;
        $arrWarningDates = [];
        foreach ($arrSkuInfo['real_stockin_info'] as $arrRealStockinInfo) {
            if (0 == $arrRealStockinInfo['amount'] && count($arrSkuInfo['real_stockin_info']) > 1) {
                Order_BusinessError::throwException(Order_Error_Code::SKU_AMOUNT_CANNOT_EMPTY);
            }
            $intSkuAmount = intval($arrRealStockinInfo['amount']);
            if(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intOrderType){
                // 销退入库校验良品状态
                $intSkuGoodAmount = intval($arrRealStockinInfo['sku_good_amount']);
                $intSkuDefectiveAmount = intval($arrRealStockinInfo['sku_defective_amount']);
                $intFromCountry = intval($sourceOrderSkuInfo['import']);
            } else {
                // 预约入库则认为全为良品
                $intSkuGoodAmount = $intSkuAmount;
                $intSkuDefectiveAmount = 0;
                $intFromCountry = intval($sourceOrderSkuInfo['sku_from_country']);
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
            // app illegal dates
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $sourceOrderSkuInfo['sku_effect_type']) {
                $boolIsMadeInChina = $intFromCountry == Nscm_Define_Sku::SKU_COUNTRY_INSIDE;
                $intProductionTime = $arrRealStockinInfo['expire_date'];
                $intSkuEffectDay = $sourceOrderSkuInfo['sku_effect_day'];
                if (!Nscm_Service_Stock::checkStockInShelfLife($intProductionTime, $intSkuEffectDay, $boolIsMadeInChina)) {
                    $arrWarningDates[] = $intProductionTime;
                }
            }
        }
        if ($intTotalAmount > $intPlanAmount) {
            // stock in order sku amount must smaller than reserve order
            Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_AMOUNT_TOO_MUCH);
        }
        $arrDbRow = [
            'stockin_order_id' => $intStockinOrderId,
            'sku_id' => $sourceOrderSkuInfo['sku_id'],
            'upc_id' => $sourceOrderSkuInfo['upc_id'],
            'upc_unit' => $sourceOrderSkuInfo['upc_unit'],
            'upc_min_unit' => $intUpcMinUnit,
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
            'sku_from_country' => $intSkuFromCountry,
            'stockin_order_sku_total_price' => $intTotalAmount * $intSkuPrice,
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $intSkuPriceTax,
            'stockout_order_sku_amount' => $intPlanAmount,  // 预约入库单 出库数 等于 计划入库数，销退入库单 出库数 等于 拣货数
            'reserve_order_sku_plan_amount' => $intPlanAmount,
            'stockin_order_sku_real_amount' => $intTotalAmount,
            'stockin_order_sku_extra_info' => json_encode($arrDbStockinOrderSkuExtraInfo),
        ];
        return [
            'db_row' => $arrDbRow,
            'warning_dates' => $arrWarningDates,
        ];
    }

    /**
     * 根据入库单号和商品编码/条码查询商品信息
     * @param $strStockinOrderId
     * @param $strSkuUpcId
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinOrderSkuInfoBySkuUpcId($strStockinOrderId, $strSkuUpcId)
    {
        $strStockinOrderId = Order_Util::trimStockinOrderIdPrefix($strStockinOrderId);

        // 判断sku_id还是upc_id
        $strSkuId = null;
        if (true == Order_Util_Sku::isSkuId($strSkuUpcId)) {
            $strSkuId = $strSkuUpcId;
        } else if (true == Order_Util_Sku::isUpcId($strSkuUpcId)) {
            // 将upc_id转换成sku_id
            $objVendorRalSku = new Dao_Ral_Sku();
            $retInfo = $objVendorRalSku->getSkuInfosByIds([$strSkuUpcId]);
            $strSkuId = $retInfo['result']['skus'][0]['sku_id'];
            if(empty($strSkuId)) {
                Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_UPC_ID_NOT_EXIST);
            }
        } else {
            Order_BusinessError::throwException(Order_Error_Code::SKU_UPC_OR_SKU_ID_LENGTH_EXCEPTION);
        }

        // 根据sku_id拿到数据库sku信息
        $arrOrderSkuInfo = Model_Orm_StockinOrderSku::getStockinOrderSkuInfo($strStockinOrderId, $strSkuId);
        if (empty($arrOrderSkuInfo)){
            Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_SKU_NOT_FOUND);
        }

        // 根据库存函数计算过期时间 / 临期日期信息
        $intSkuEffectType = intval($arrOrderSkuInfo['sku_effect_type']);
        $intSkuEffectDay = intval($arrOrderSkuInfo['sku_effect_day']);
        $intSkuFromCountry = intval($arrOrderSkuInfo['sku_from_country']);
        $arrRet = $arrOrderSkuInfo;
        $arrRet['critical_time'] = Nscm_Service_Stock::calculateShelfLifeTime($intSkuEffectType, $intSkuEffectDay);
        $arrRet['product_expire_time'] =
            Nscm_Service_Stock::calculateProductionTimeByNowEffectDay($intSkuEffectType, $intSkuEffectDay);

        return $arrRet;
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
     * @param bool $boolIgnoreCheckDate
     * @return array
     * @throws Order_BusinessError
     */
    private function getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList, $intType,
                                      $boolIgnoreCheckDate)
    {
        // pre treat sku
        $arrHashReserveOrderSkus = [];
        foreach ($arrReserveOrderSkus as $arrSku) {
            $arrHashReserveOrderSkus[$arrSku['sku_id']] = $arrSku;
        }
        $arrDbSkuInfoList = [];
        $arrWarningInfo = [];

        foreach ($arrSkuInfoList as $arrSkuInfo) {
            if (!isset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']])) {
                // sku id not in purchase order or sku id repeat
                Order_BusinessError::throwException(Order_Error_Code::SKU_ID_NOT_EXIST_OR_SKU_ID_REPEAT);
            }
            $arrReserveOrderSku = $arrHashReserveOrderSkus[$arrSkuInfo['sku_id']];
            $arrFormatInfo = $this->formatStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo, $intType);
            $arrSkuRow = $arrFormatInfo['db_row'];
            $arrWarningDates = $arrFormatInfo['warning_dates'];
            // illegal date
            if (!empty($arrWarningDates)) {
                $arrWarningInfo[$arrSkuInfo['sku_id']] = $arrWarningDates;
            }
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
        if (!$boolIgnoreCheckDate && !empty($arrWarningInfo)) {
            Bd_Log::trace(sprintf('throw illegal sku production date, check[%s], info[%s]',
                json_encode($boolIgnoreCheckDate), json_encode($arrWarningInfo)));
            Order_BusinessError::throwException(Order_Error_Code::NOT_IGNORE_ILLEGAL_DATE, '', $arrWarningInfo);
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
     * @param int $boolIgnoreCheckDate
     * @param $intStockinDevice
     * @return int
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId, $strStockinOrderRemark,
                                       $arrSkuInfoList, $intCreatorId, $strCreatorName, $intType, $boolIgnoreCheckDate,
                                        $intStockinDevice)
    {
        $strContent = '';
        if (!isset(Order_Define_StockinOrder::STOCKIN_ORDER_TYPES[$intType])) {
            // order type error
            Order_Error::throwException(Order_Error_Code::SOURCE_ORDER_TYPE_ERROR);
        }
        $boolIgnoreCheckDate = boolval($boolIgnoreCheckDate);
        $intStockinOrderId = Order_Util_Util::generateStockinOrderCode();
        $arrSourceOrderSkus = $this->assemblePrice($intWarehouseId, $arrSourceOrderSkus, $intType);
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrSourceOrderSkus, $arrSkuInfoList, $intType,
            $boolIgnoreCheckDate);
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
            $intLogType = Order_Define_Const::APP_NWMS_ORDER_LOG_STOCKIN_RESERVE_TYPE;
            $strOrderId = strval($intSourceOrderId);
            $strContent = Order_Define_Text::SUBMIT_STOCKIN_RESERVE_ORDER;
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
            $intLogType = Order_Define_Const::APP_NWMS_ORDER_LOG_STOCKIN_STOCKOUT_TYPE;
            $strOrderId = strval($intStockinOrderId);
            $strContent = Order_Define_Text::SUBMIT_STOCKIN_STOCKOUT_ORDER;
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
        $intStockinDevice = intval($intStockinDevice);
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($intStockinOrderId, $intStockinOrderType,
            $intStockInOrderDataSourceType, $intStockinOrderSource, $intSourceOrderId, $strSourceSupplierId, $strSourceInfo, $intStockinOrderStatus,
            $intWarehouseId, $strWarehouseName, $intCityId, $strCityName, $intStockinTime, $intReserveOrderPlanTime,
            $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
            $strStockinOrderRemark, $arrDbSkuInfoList, $intStockinOrderTotalPrice, $intStockinOrderTotalPriceTax,
            $intCustomerId,$strCustomerName, $intStockinDevice) {
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
                $strCustomerName,
                $intStockinDevice);
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
            // drop redis key
            try {
                $daoRedis = new Dao_Redis_StockInOrder();
                $daoRedis->dropOperateRecord(Nscm_Define_OrderPrefix::ASN . $intSourceOrderId);
            } catch (Exception $e) {
                Bd_Log::warning('drop_redis_key_error: ' . $e->getMessage());
            }
        }
        $strOperateDevice = Order_Define_Const::DEVICE_MAP[$intStockinDevice];
        $intOperateType = Dao_Ral_Log::LOG_OPERATION_TYPE_UPDATE;
        $this->addLog($intLogType, $strOrderId, $strOperateDevice, $strContent, $intCreatorId, $strCreatorName,
            $intOperateType);
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
                'unit' => strval(Nscm_Define_Sku::UPC_UNIT_MAP[$arrSku['upc_unit']]),
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
                $intProductionTime = 0;
                $intExpireTime = 0;
                if (Order_Define_Sku::SKU_EFFECT_TYPE_PRODUCT == $arrDbSku['sku_effect_type']) {
                    $intProductionTime = strtotime(date('Ymd', $skuRow['expire_date']));
                    $intExpireTime = $intProductionTime + intval($arrDbSku['sku_effect_day']) * 86400 - 1;
                } else {
                    $intExpireTime = strtotime(date('Ymd', $skuRow['expire_date'])) + 86399;
                }

                // 良品数
                if(0 < $skuRow['sku_good_amount']) {
                    $arrBatchInfo[] = [
                        'expire_time' => $intExpireTime,
                        'production_time' => $intProductionTime,
                        'is_defective' => Order_Define_Sku::SKU_QUALITY_TYPE_GOOD,
                        'amount'      => $skuRow['sku_good_amount'],
                    ];
                }

                // 不良品数
                if(0 < $skuRow['sku_defective_amount']) {
                    $arrBatchInfo[] = [
                        'expire_time' => $intExpireTime,
                        'production_time' => $intProductionTime,
                        'is_defective' => Order_Define_Sku::SKU_QUALITY_TYPE_DEFECTIVE,
                        'amount'      => $skuRow['sku_defective_amount'],
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
        $intIsPlacedOrder,
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
            $intIsPlacedOrder,
            $intPageNum,
            $intPageSize);
    }

    /**
     * 查询入库单详情（入库单号或者运单号）
     * @param $strOrderId
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getStockinOrderInfoByStockinOrderId($strOrderId)
    {
        $arrRet = [];
        $intOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strOrderId));
        $strStockinOrderId = null;
        if (true == Order_Util::isStockinOrderId($strOrderId)) {
            $arrRet = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($intOrderId);
            $strStockinOrderId = Nscm_Define_OrderPrefix::SIO . $arrRet['stockin_order_id'];
        } else if (true == Order_Util::isShipmentOrderId($strOrderId)) {
            $arrRet = Model_Orm_StockinOrder::getStockinOrderInfoByShipmentOrderId($strOrderId);
            $strStockinOrderId = Nscm_Define_OrderPrefix::SIO . $arrRet['stockin_order_id'];
        } else {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        // 存在入库单为空的场景，校验查出来的单号是否合法
        if (true == Order_Util::isStockinOrderId($strStockinOrderId)) {
            // 检查当前用户id的操作记录，返回操作信息
            $objDaoRedis = new Dao_Redis_StockInOrder();
            $arrOrderOperateRecord = $objDaoRedis->getOperateRecord($strStockinOrderId);
            if (!empty($arrOrderOperateRecord)) {
                // 取出最后一条操作记录信息
                $arrLastOperateRecord = end($arrOrderOperateRecord);
                $arrRet['last_operate_record'] = $arrLastOperateRecord;
            }
        }

        return $arrRet;
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
        $arrSkuColumns = ['stockin_order_id','upc_id','sku_name','sku_net','upc_unit','upc_min_unit','reserve_order_sku_plan_amount','stockin_order_sku_real_amount','sku_net_unit','stockin_order_sku_extra_info'];
        $arrReserveSkuList = Model_Orm_StockinOrderSku::findRows($arrSkuColumns, $arrConditions);
        $arrReserveSkuList = $this->arrayToKeyValue($arrReserveSkuList, 'stockin_order_id');
        foreach ($arrRetList as $key=>$item) {
            $arrSourceInfo =empty($item['source_info']) ? []:json_decode($item['source_info'],true);
            $arrRetList[$key]['vendor_id'] = isset($arrSourceInfo['vendor_id']) ? $arrSourceInfo['vendor_id']:0;
            $arrRetList[$key]['vendor_name'] = isset($arrSourceInfo['vendor_name']) ? $arrSourceInfo['vendor_name']:'';
            $arrRetList[$key]['customer_id'] = isset($arrSourceInfo['customer_id']) ? $arrSourceInfo['customer_id']:0;
            $arrRetList[$key]['customer_contactor'] = isset($arrSourceInfo['customer_contactor']) ? $arrSourceInfo['customer_contactor']:'';
            $arrRetList[$key]['customer_contact'] = isset($arrSourceInfo['customer_contact']) ? $arrSourceInfo['customer_contact']:'';
            $arrRetList[$key]['customer_address'] = isset($arrSourceInfo['customer_address']) ? $arrSourceInfo['customer_address']:'';
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
        $arrSkuInfoList  = [];
        $arrSkuPriceList = [];
        $arrDbSkuInfoList = [];
        if (!empty($arrRequestSkuInfoList)) {
            $arrSkuInfoList = $this->getSkuInfoList($arrSkuIds);
            $arrSkuPriceList = $this->getSkuPrice($arrSkuIds, $intWarehouseId, $arrSkuInfoList);
            $arrDbSkuInfoList = $this->assembleWithdrawDbSkuList($arrRequestSkuInfoMap, $arrSkuInfoList,
                $arrSkuPriceList);
        }
        $intOrderReturnReason = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REMOVE_SITE;
        $strOrderReturnReasonText = Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[$intOrderReturnReason];
        $intStockinOrderPlanAmount = $this->calculateTotalSkuPlanAmount($arrDbSkuInfoList);
        $intStockinOrderTotalPrice = $this->calculateTotalPrice($arrDbSkuInfoList);
        $intStockinOrderTotalPriceTax = $this->calculateTotalPriceTax($arrDbSkuInfoList);
        $arrSourceInfo = $arrInput['customer_info'];
        $strSourceSupplierId = $strCustomerId = $arrSourceInfo['id'];
        $strCustomerName = $arrSourceInfo['name'];
        $arrSourceInfo['customer_id'] = $arrSourceInfo['id'];
        $arrSourceInfo['customer_name'] = $arrSourceInfo['name'];
        $arrSourceInfo['customer_contactor'] = $arrSourceInfo['contactor'];
        $arrSourceInfo['customer_contact'] = $arrSourceInfo['contact'];
        $arrSourceInfo['customer_address'] = $arrSourceInfo['address'];
        unset($arrSourceInfo['id']);
        unset($arrSourceInfo['name']);
        unset($arrSourceInfo['contactor']);
        unset($arrSourceInfo['contact']);
        unset($arrSourceInfo['address']);
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
        $assetInformation = json_encode($assetInformation);
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
            $intSkuKindAmount = count($arrDbSkuInfoList);
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
                $strSourceSupplierId,
                $intSkuKindAmount);
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
        if (empty($arrSkuIds)) {
            return [];
        }
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
                'upc_min_unit' => $arrSkuInfo['min_upc']['upc_unit'],
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
            $strSkuMainImage = $arrSkuInfo['sku_image'][0]['url'];
            foreach ($arrSkuInfo['sku_image'] as $arrImage) {
                if (true == $arrImage['is_master']) {
                    $strSkuMainImage = $arrImage['url'];
                    break;
                }
            }
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
                'sku_from_country' => $arrSkuInfo['sku_from_country'],
                'reserve_order_sku_plan_amount' => $arrRequestSkuInfoList[$intSkuId],
                'stockout_order_sku_amount' => 0,
                'upc_min_unit' => $arrSkuInfo['min_upc']['upc_unit'],
                'sku_main_image' => strval($strSkuMainImage),
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
     * 获取仓库是否开启库区库位
     * @param int $intWarehouseId
     * @return bool
     */
    private function getWarehouseLocationTag($intWarehouseId)
    {
        $daoWrpcWarehouse = new Dao_Wrpc_Warehouse();
        $arrWarehouseInfo = $daoWrpcWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        return $arrWarehouseInfo['storage_location_tag'];
    }

    /**
     * @param  string $strStockInOrderId 入库单id
     * @param  array $arrSkuInfoList 入库sku信息
     * @param  string $strRemark 入库备注
     * @param  bool $boolIgnoreCheckDate
     * @param  int $intDeviceType device type
     * @param $intUserId
     * @param $strUserName
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function confirmStockInOrder($strStockInOrderId, $arrSkuInfoList, $strRemark, $boolIgnoreCheckDate,
                                        $intDeviceType, $intUserId, $strUserName)
    {
        $boolIgnoreCheckDate = boolval($boolIgnoreCheckDate);
        if (empty($strStockInOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
//        if (empty($arrSkuInfoList)) {
//            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
//        }
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
        //只有撤点的订单允许sku数量为0
        if (Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_REMOVE_SITE != $arrStockInOrderInfo['stockin_order_reason']
            && empty($arrSkuInfoList)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $intWarehouseId = $arrStockInOrderInfo['warehouse_id'];
        if (Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH != $arrStockInOrderInfo['stockin_order_status']) {
            $intStockInTime = time();
            $intStockInOrderRealAmount = $this->calculateStockInOrderRealAmount($arrSkuInfoList);
            //重新获取成本价
            $arrDbStockInSkuList = $this->getStockinOrderSkuList($intStockInOrderId, 1, 0);
            $arrDbStockInSkuMap = [];
            foreach ($arrDbStockInSkuList['list'] as $arrDbStockInSkuInfo) {
                $arrDbStockInSkuMap[$arrDbStockInSkuInfo['sku_id']] = $arrDbStockInSkuInfo;
            }
            $arrSkuIds = array_keys($arrDbStockInSkuMap);
            $arrSkuBaseInfoList = $this->getSkuInfoList($arrSkuIds);
            $arrSkuPriceList = $this->getSkuPrice($arrSkuIds, $intWarehouseId, $arrSkuBaseInfoList);

            $arrStockInSkuList = $this->getStockInSkuList($arrDbStockInSkuMap, $arrSkuInfoList, $boolIgnoreCheckDate, $arrSkuPriceList);
            $arrDbSkuInfoList = $this->assembleDbSkuInfoList($arrSkuInfoList, $intStockInOrderId, $arrSkuPriceList);
            list($intRealPriceAmount, $intRealPriceTaxAmount) = $this->assembleStockInOrderRealPrice($arrSkuInfoList, $arrSkuPriceList);
            Model_Orm_StockinOrder::getConnection()->transaction(function () use (
                $intStockInOrderId, $intStockInTime, $intStockInOrderRealAmount, $arrDbSkuInfoList, $strRemark,
                $intWarehouseId, $arrStockInSkuList, $intDeviceType, $intRealPriceAmount, $intRealPriceTaxAmount) {
                $intBatchId = 0;
                if (!empty($arrStockInSkuList)) {
                    $objRalStock = new Dao_Ral_Stock();
                    $arrStock = $objRalStock->stockIn($intStockInOrderId,
                        Nscm_Define_Stock::STOCK_IN_TYPE_SALE_RETURN,
                        $intWarehouseId,
                        $arrStockInSkuList);
                    $intBatchId = $arrStock['stockin_batch_id'];
                }
                Model_Orm_StockinOrder::confirmStockInOrder($intStockInOrderId, $intStockInTime,
                    $intStockInOrderRealAmount, $strRemark, $intBatchId, $intDeviceType,
                $intRealPriceAmount, $intRealPriceTaxAmount);
                Model_Orm_StockinOrderSku::confirmStockInOrderSkuList($arrDbSkuInfoList);
            });
            $intTable = Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT;
            $intType = Order_Statistics_Type::ACTION_CREATE;
            //数量不为0才同步报表
            if (!empty($intStockInOrderRealAmount)) {
                Dao_Ral_Statistics::syncStatistics($intTable, $intType, $intStockInOrderId);
            }
            if (!empty($arrStockInOrderInfo['shipment_order_id'])) {
                $this->sendConfirmStockinOrderInfoToOms($intStockInOrderId, $arrStockInOrderInfo['shipment_order_id'], $arrStockInOrderInfo['stockin_order_source'], $arrSkuInfoList);
            }
            //判断是否开启库区库位
            $intWarehouseLocationTag = $this->getWarehouseLocationTag($intWarehouseId);
            if (Order_Define_Warehouse::STORAGE_LOCATION_TAG_DISABLE == $intWarehouseLocationTag && !empty($arrStockInSkuList)) {
                $arrInput['stockin_order_ids'] = $intStockInOrderId;
                $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_PLACE_ORDER_CREATE, $arrInput);
                if (false == $ret) {
                    Bd_Log::warning("send wmq failed arrInput[%s] cmd[%s]",
                        json_encode($arrInput), Order_Define_Cmd::CMD_PLACE_ORDER_CREATE);
                }
            }
        }
        try {
            $daoRedis = new Dao_Redis_StockInOrder();
            $daoRedis->dropOperateRecord(Nscm_Define_OrderPrefix::SIO . $intStockInOrderId);
        } catch (Exception $e) {
            Bd_Log::warning('drop_redis_key_error: ' . $e->getMessage());
        }
        $intLogType = Order_Define_Const::APP_NWMS_ORDER_LOG_STOCKIN_STOCKOUT_TYPE;
        $strOrderId = strval($intStockInOrderId);
        $strOperateDevice = Order_Define_Const::DEVICE_MAP[$intDeviceType];
        $strContent = Order_Define_Text::SUBMIT_STOCKIN_STOCKOUT_ORDER;
        $intOperateType = Dao_Ral_Log::LOG_OPERATION_TYPE_UPDATE;
        $this->addLog($intLogType, $strOrderId, $strOperateDevice, $strContent, $intUserId, $strUserName,
            $intOperateType);
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

    /**
     * 构建更新入库单sku信息
     * @param $arrSkuInfoList
     * @param $intStockInOrderId
     * @param $arrSkuPriceList
     * @return array
     */
    private function assembleDbSkuInfoList($arrSkuInfoList, $intStockInOrderId, $arrSkuPriceList)
    {
        $arrDbSkuInfoList = [];
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            $arrSkuRealInfoList = $arrSkuInfo['real_stockin_info'];
            $intStockInSkuRealAmount = array_sum(array_column($arrSkuRealInfoList, 'amount'));
            $arrDbSkuInfo = [
                'stockin_order_id' => $intStockInOrderId,
                'sku_id' => $arrSkuInfo['sku_id'],
                'sku_price' => $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price'],
                'stockin_order_sku_total_price' => $intStockInSkuRealAmount * $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price'],
                'sku_price_tax' => $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price_tax'],
                'stockin_order_sku_total_price_tax' => $intStockInSkuRealAmount * $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price_tax'],
                'stockin_order_sku_real_amount' => $intStockInSkuRealAmount,
                'stockin_order_sku_extra_info' => json_encode($arrSkuRealInfoList),
            ];
            $arrDbSkuInfoList[] = $arrDbSkuInfo;
        }
        return $arrDbSkuInfoList;
    }

    /**
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
     * @param  array  $arrDbStockInSkuMap
     * @param  array $arrSkuInfoListRequest
     * @param bool $boolIgnoreCheckDate
     * @param array $arrSkuPriceList
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    private function getStockInSkuList($arrDbStockInSkuMap, $arrSkuInfoListRequest, $boolIgnoreCheckDate, $arrSkuPriceList)
    {
        $arrStockInSkuList = [];
        $arrWarningInfo = [];
        foreach ($arrSkuInfoListRequest as $arrSkuInfo) {
            if (!isset($arrDbStockInSkuMap[$arrSkuInfo['sku_id']])) {
                Order_Error::throwException(Order_Error_Code::SYS_ERROR);
            }
            $arrRealSkuInfo = $arrSkuInfo['real_stockin_info'];
            $arrStockInSkuListItem = [
                'sku_id' => $arrSkuInfo['sku_id'],
                'unit_price' => $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price'],
                'unit_price_tax' => $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price_tax'],
            ];
            $intSkuEffectType = $arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['sku_effect_type'];
            $intSkuEffectDate = intval($arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['sku_effect_day']);
            foreach ($arrRealSkuInfo as $arrSkuInfoItem) {
                if (Order_Define_Sku::SKU_EFFECT_TYPE_PRODUCT == $intSkuEffectType) {
                    $intProductionTime = strtotime(date('Ymd', $arrSkuInfoItem['expire_date']));
                    $intExpireTime = $intProductionTime + $intSkuEffectDate * 86400 - 1;
                    $boolIsMadeInChina = $arrDbStockInSkuMap[$arrSkuInfo['sku_id']]['sku_from_country'] ==
                        Nscm_Define_Sku::SKU_COUNTRY_INSIDE;
                    if (!Nscm_Service_Stock::checkStockInShelfLife($arrSkuInfoItem['expire_date'], $intSkuEffectDate,
                        $boolIsMadeInChina)) {
                        $arrWarningInfo[$arrSkuInfo['sku_id']][] = $arrSkuInfoItem['expire_date'];
                    }

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
        if (!$boolIgnoreCheckDate && !empty($arrWarningInfo)) {
            Bd_Log::trace(sprintf('throw illegal sku production date, check[%s], info[%s]',
                json_encode($boolIgnoreCheckDate), json_encode($arrWarningInfo)));
            Order_BusinessError::throwException(Order_Error_Code::NOT_IGNORE_ILLEGAL_DATE, '', $arrWarningInfo);
        }
        return $arrStockInSkuList;
    }

    /**
     * @param $strOrderId
     * @param $strOperateName
     * @param $intUserId
     * @param $strOperateDevice
     * @return bool
     * @throws Order_BusinessError
     */
    public function addOrderOperateRecord($strOrderId, $strOperateName, $intUserId, $strOperateDevice)
    {
        $daoRedis = new Dao_Redis_StockInOrder();
        $intOperateTime = time();
        $strOperateName = strval($strOperateName);
        $strOperateDevice = strval($strOperateDevice);
        $strOrderId = strval($strOrderId);
        $intUserId = intval($intUserId);
        $intLogType = 0;
        $intOrderId = 0;
        // check order status
        if (preg_match('/(SIO|ASN)(\d{13})/', $strOrderId, $arrMatches)) {
            $strOrderPrefix = $arrMatches[1];
            $intOrderId = $arrMatches[2];
            if (Nscm_Define_OrderPrefix::ASN == $strOrderPrefix) {
                // reserve order
                $arrReserveOrderInfo = Model_Orm_ReserveOrder::getReserveOrderInfoByReserveOrderId($intOrderId);
                if (empty($arrReserveOrderInfo)) {
                    Bd_Log::warning('reserve order not exist: ' . $intOrderId);
                    Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
                }
                if (Order_Define_ReserveOrder::STATUS_STOCKING != $arrReserveOrderInfo['reserve_order_status']) {
                    Bd_Log::warning('reserve order status invalid: ' . $arrReserveOrderInfo['reserve_order_status']);
                    Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN);
                }
                $intLogType = Order_Define_Const::APP_NWMS_ORDER_LOG_STOCKIN_RESERVE_TYPE;
            } else {
                $arrStockinOrderInfo = Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderId($intOrderId);
                if (empty($arrStockinOrderInfo)) {
                    Bd_Log::warning('stockin order not exist: ' . $intOrderId);
                    Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
                }
                if (Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_WAITING
                    != $arrStockinOrderInfo['stockin_order_status']) {
                    Bd_Log::warning('stockin order status invalid: ' . $arrStockinOrderInfo['stockin_order_status']);
                    Order_BusinessError::throwException(Order_Error_Code::STOCKIN_ORDER_STATUS_INVALID);
                }
                $intLogType = Order_Define_Const::APP_NWMS_ORDER_LOG_STOCKIN_STOCKOUT_TYPE;
            }
        } else {
            Bd_Log::trace('order rules not allow: ' . $strOrderId);
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $arrOperateRecord = $daoRedis->getOperateRecord($strOrderId);
        if (empty($arrOperateRecord)) {
            $strContent = Order_Define_Text::START_OPERATE_STOCKIN_ORDER;
            $intOperateType = Dao_Ral_Log::LOG_OPERATION_TYPE_CREATE;
            $this->addLog($intLogType, $intOrderId, $strOperateDevice, $strContent, $intUserId, $strOperateName,
                $intOperateType);
        }
        $boolResult = $daoRedis->addOperateRecord($strOrderId, $strOperateName, $strOperateDevice, $intOperateTime,
            $intUserId);
        return $boolResult;
    }

    /**
     * @param $intLogType
     * @param $intOrderId
     * @param $strDevice
     * @param $strContent
     * @param $intUserId
     * @param $strUserName
     * @param $intOperateType
     */
    public function addLog($intLogType, $intOrderId, $strDevice, $strContent, $intUserId, $strUserName, $intOperateType)
    {
        $daoLog = new Dao_Ral_Log();
        $arrLog = [
            'content' => $strContent,
            'device' => $strDevice,
        ];
        $strLog = json_encode($arrLog, JSON_UNESCAPED_UNICODE);
        $daoLog->addLog($intLogType, intval($intOrderId), $intOperateType, $strUserName, $intUserId, $strLog);
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

    /**
     * 批量查询入库单详情
     * @param $arrStockinOrderIds
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getStockinOrderInfoByStockinOrderIds($arrStockinOrderIds)
    {
        if (empty($arrStockinOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        return Model_Orm_StockinOrder::getStockinOrderInfoByStockinOrderIds($arrStockinOrderIds);
    }

    /**
     * 批量查询入库单商品列表（不分页）
     * @param $arrStockinOrderIds
     * @return array
     * @throws Order_BusinessError
     */
    public function getBatchStockinOrderSkus($arrStockinOrderIds)
    {
        $arrRetStockinOrderSkus = [
            'total' => 0,
            'list' => [],
        ];

        if (empty($arrStockinOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrStockinOrderSkus = Model_Orm_StockinOrderSku::getBatchStockinOrderSkus($arrStockinOrderIds);

        // 将查询的商品按照入库单维度进行封装
        if (!empty($arrStockinOrderSkus)) {
            $arrRetStockinOrderSkus['total'] = $arrStockinOrderSkus['total'];
            $arrStockinOrderSkusList = $arrStockinOrderSkus['list'];
            $arrOrderSkusInfoList = [];
            if (!empty($arrStockinOrderSkusList)) {
                foreach ($arrStockinOrderSkusList as $skuInfo) {
                    if (!empty($skuInfo['stockin_order_id'])) {
                        $arrOrderSkusInfoList[$skuInfo['stockin_order_id']][] = $skuInfo;
                    }
                }
            }

            $arrRetStockinOrderSkus['list'] = $arrOrderSkusInfoList;
        }
        return $arrRetStockinOrderSkus;
    }

    /**
     * 获取入库单sku信息
     * @param $intStockinOrderId
     * @param $intSkuId
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinOrderSkuInfo($intStockinOrderId, $intSkuId)
    {
        if (empty($intStockinOrderId) || empty($intSkuId)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        $objSkuInfo = Model_Orm_StockinOrderSku::getStockinOrderSkuInfoObject($intStockinOrderId, $intSkuId);
        if (empty($objSkuInfo)) {
            return [];
        }
        return $objSkuInfo->toArray();
    }

    /**
     * 构建计算入库单的真实入库总金额
     * @param array $arrSkuInfoList
     * @param array $arrSkuPriceList
     * @return array [$intRealPriceAmount, $intRealPriceTaxAmount]
     */
    private function assembleStockInOrderRealPrice($arrSkuInfoList, $arrSkuPriceList)
    {
        $intRealPriceAmount = 0;
        $intRealPriceTaxAmount = 0;
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            $arrSkuRealInfoList = $arrSkuInfo['real_stockin_info'];
            $intStockInSkuRealAmount = array_sum(array_column($arrSkuRealInfoList, 'amount'));
            $intRealPriceAmount += $intStockInSkuRealAmount * $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price'];
            $intRealPriceTaxAmount += $intStockInSkuRealAmount * $arrSkuPriceList[$arrSkuInfo['sku_id']]['sku_price_tax'];
        }
        return [
            $intRealPriceAmount,
            $intRealPriceTaxAmount,
        ];
    }
}
<?php
/**
 * @name Service_Data_Reserve_ReserveOrder
 * @desc Service_Data_Reserve_ReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Reserve_ReserveOrder
{

    /**
     * get order stocking count
     * @param int[] $arrWarehouseIds
     * return int
     */
    public function getOrderStockingCount($arrWarehouseIds)
    {
        $arrIntWarehouseIds = [];
        foreach ((array)$arrWarehouseIds as $intWarehouseId)
        {
            $arrIntWarehouseIds[] = intval($intWarehouseId);
        }
        $intCount = Model_Orm_ReserveOrder::getWarehouseStatusCount($arrIntWarehouseIds, Order_Define_ReserveOrder::STATUS_STOCKING);
        return $intCount;
    }

    /**
     * destroy reserve order
     * @param $intPurchaseOrderId
     * @param $intDestroyType
     * @throws Order_BusinessError
     */
    public function destroyReserveOrder($intPurchaseOrderId, $intDestroyType)
    {
        $intStatus = Order_Define_ReserveOrder::NSCM_DESTROY_STATUS[$intDestroyType];
        if (empty($intStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $objReserveOrder = Model_Orm_ReserveOrder::getReserveInfoByPurchaseOrderId($intPurchaseOrderId);
        if (empty($objReserveOrder)) {
            Order_BusinessError::throwException(Order_Error_Code::PURCHASE_ORDER_NOT_EXIST);
        }
        if (!isset(Order_Define_ReserveOrder::ALLOW_DESTROY[$objReserveOrder->reserve_order_status])) {
            Order_BusinessError::throwException(Order_Error_Code::PURCHASE_ORDER_NOT_ALLOW_DESTROY);
        }
        $objReserveOrder->updateStatus($intStatus);
    }

    /**
     * assemble sku
     * @param array $arrSourceSku
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_Error
     */
    private function assembleSku($arrSourceSku)
    {
        $arrSkuId = [];
        foreach ($arrSourceSku as $row)
        {
            $arrSkuId[] = $row['sku_id'];
        }
        $daoRalSku = new Dao_Ral_Sku();
        $arrSkuInfo = $daoRalSku->getSkuInfos($arrSkuId);
        $arrRes = [];
        foreach ($arrSourceSku as $row)
        {
            $arrRes[] = [
                'sku_id' => $row['sku_id'],
                'upc_id' => $row['upc_id'],
                'upc_unit' => $row['upc_unit'],
                'upc_unit_num' => $row['upc_unit_num'],
                'sku_name' => $arrSkuInfo[$row['sku_id']]['sku_name'] ?? '',
                'sku_net' => $arrSkuInfo[$row['sku_id']]['sku_net'] ?? '',
                'sku_net_unit' => $arrSkuInfo[$row['sku_id']]['sku_net_unit'] ?? '',
                'sku_net_gram' => $arrSkuInfo[$row['sku_id']]['sku_weight'] ?? '',
                'sku_price' => $row['sku_price'],
                'sku_price_tax' => $row['sku_price_tax'],
                'sku_tax_rate' => $arrSkuInfo[$row['sku_id']]['sku_tax_rate'] ?? 0,
                'sku_effect_type' => $arrSkuInfo[$row['sku_id']]['sku_effect_type'] ?? '',
                'sku_effect_day' => $arrSkuInfo[$row['sku_id']]['sku_effect_day'] ?? '',
                'reserve_order_sku_total_price' => $row['reserve_order_sku_total_price'],
                'reserve_order_sku_total_price_tax' => $row['reserve_order_sku_total_price_tax'],
                'reserve_order_sku_plan_amount' => $row['reserve_order_sku_plan_amount'],
            ];
        }
        return $arrRes;
    }

    /**
     * create reserve order by nscm reserve order id
     * @param int $intPurchaseOrderId
     * @throws Nscm_Exception_Error
     * @throws Order_Error
     * @throws Exception
     */
    public function createReserveOrderByPurchaseOrderId($intPurchaseOrderId)
    {
        $objRedis = new Dao_Redis_ReserveOrder();
        $arrOrderInfo = $objRedis->getOrderInfo($intPurchaseOrderId);
        $arrSkus = $this->assembleSku($arrOrderInfo['purchase_order_skus']);
        $arrIllegalSkus = $this->checkIllegalSku($arrSkus);
        if (!empty($arrIllegalSkus)) {
            // @alarm
            Bd_Log::warning(sprintf('get skus info fail, sku: %s, purchase order id: %d',
                implode(',', $arrIllegalSkus), $intPurchaseOrderId));
            Order_Error::throwException(Order_Error_Code::RAL_ERROR);
        }
        Bd_Log::debug('order info: ' . json_encode($arrOrderInfo));
        if (empty($arrOrderInfo)) {
            // @alarm
            Bd_Log::warning('can`t find nscm purhcase order id: ' . $intPurchaseOrderId);
            return;
        }
        Model_Orm_ReserveOrder::getConnection()->transaction(function () use ($arrOrderInfo, $intPurchaseOrderId, $arrSkus) {
            $intReserveOrderId = intval($arrOrderInfo['reserve_order_id']);
            $intWarehouseId = intval($arrOrderInfo['warehouse_id']);
            $strWarehouseName = strval($arrOrderInfo['warehouse_name']);
            $intReserveOrderPlanTime = intval($arrOrderInfo['purchase_order_plan_time']);
            $intReserveOrderPlanAmount = intval($arrOrderInfo['purchase_order_plan_amount']);
            $intVendorId = intval($arrOrderInfo['vendor_id']);
            $strVendorName = strval($arrOrderInfo['vendor_name']);
            // following 4 params are not used.  from pm liang yubiao
            $strVendorContactor = '';
            $strVendorMobile = '';
            $strVendorEmail = '';
            $strVendorAddress = '';
            $strReserveOrderRemark = strval($arrOrderInfo['purchase_order_remark']);
            Model_Orm_ReserveOrder::createReserveOrder($intReserveOrderId, $intPurchaseOrderId, $intWarehouseId,
                $strWarehouseName, $intReserveOrderPlanTime, $intReserveOrderPlanAmount, $intVendorId, $strVendorName,
                $strVendorContactor, $strVendorMobile, $strVendorEmail, $strVendorAddress, $strReserveOrderRemark);
            Bd_Log::debug('ORDER_SKUS:' . json_encode($arrSkus));
            Model_Orm_ReserveOrderSku::createReserveOrderSku($arrSkus, $intReserveOrderId);
        });
        $objRedis->dropOrderInfo($intPurchaseOrderId);
    }

    /**
     * check illegal sku
     * @param array $arrSkus
     * @return int[]
     */
    private function checkIllegalSku($arrSkus)
    {
        $arrRet = [];
        foreach ($arrSkus as $row) {
            if (!isset(Order_Define_Sku::SKU_EFFECT_TYPE_EXPIRE_MAP[$row['sku_effect_type']])) {
                $arrRet[] = $row['sku_id'];
            }
        }
        return $arrRet;
    }

    /**
     * generate reserve order id
     * @param int $intPurchaseOrderId
     * @return int
     * @throws Order_BusinessError
     */
    public function generateReserveOrderId($intPurchaseOrderId)
    {
        $intReserveOrderId = $this->getReserveOrderIdByPurchaseOrderId($intPurchaseOrderId);
        if (!empty($intReserveOrderId)) {
            Bd_Log::warning('nscm reserve order has already been received, id: ' . $intPurchaseOrderId);
            $arrExtra = [
                'reserve_order_id' => $intReserveOrderId,
            ];
            Order_BusinessError::throwException(Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED, '', $arrExtra);
        } else {

            Bd_Log::trace('generate reserve order id by nscm reserve order id: ' . $intPurchaseOrderId);
            $intReserveOrderId = Order_Util_Util::generateReserveOrderCode();
            Bd_Log::debug(sprintf('generate reserve order id[%s] by nscm reserve order id[%s]',
                $intReserveOrderId, $intPurchaseOrderId));
        }
        return $intReserveOrderId;
    }

    /**
     * send create reserve order
     * @param $arrReserveOrder
     * @return array
     * @throws Order_BusinessError
     */
    public function saveCreateReserveOrder($arrReserveOrder)
    {
        $intPurchaseOrderId = intval($arrReserveOrder['purchase_order_id']);
        $intReserveOrderId = $this->generateReserveOrderId($intPurchaseOrderId);
        $arrReserve = [
            'reserve_order_id' => $intReserveOrderId,
            'purchase_order_id' => $intPurchaseOrderId,
            'warehouse_id' => intval($arrReserveOrder['warehouse_id']),
            'warehouse_name' => strval($arrReserveOrder['warehouse_name']),
            'purchase_order_plan_time' => intval($arrReserveOrder['purchase_order_plan_time']),
            'purchase_order_plan_amount' => intval($arrReserveOrder['purchase_order_plan_amount']),
            'vendor_id' => intval($arrReserveOrder['vendor_id']),
            'vendor_name' => strval($arrReserveOrder['vendor_name']),
//            'vendor_contactor' => strval($arrReserveOrder['vendor_contactor']),
//            'vendor_mobile' => strval($arrReserveOrder['vendor_mobile']),
//            'vendor_email' => strval($arrReserveOrder['vendor_email']),
//            'vendor_address' => strval($arrReserveOrder['vendor_address']),
            'reserve_order_remark' => strval($arrReserveOrder['reserve_order_remark']),
            'purchase_order_skus' => $arrReserveOrder['purchase_order_skus'],
        ];
        $objRedis = new Dao_Redis_ReserveOrder();
        $key = $objRedis->setOrderInfo($arrReserve);
        $arrRet = [
            'key' => $key,
            'purchase_order_id' => $intReserveOrderId,
        ];
        return $arrRet;
    }

    /**
     * get reserve order id by purchase order id
     * @param $intPurchaseOrderId
     * @return int
     */
    public function getReserveOrderIdByPurchaseOrderId($intPurchaseOrderId)
    {
        $strPurchaseOrderId = strval($intPurchaseOrderId);
        // check redis
        $objRedis = new Dao_Redis_ReserveOrder();
        $arrRedisOrderInfo = $objRedis->getOrderInfo($strPurchaseOrderId);
        if (!empty($arrRedisOrderInfo['reserve_order_id'])) {
            return intval($arrRedisOrderInfo['reserve_order_id']);
        }
        // check database
        $objDbOrderInfo = Model_Orm_ReserveOrder::getReserveInfoByPurchaseOrderId($intPurchaseOrderId);
        if (!empty($objDbOrderInfo)) {
            return $objDbOrderInfo->reserve_order_id;
        }
        return 0;
    }

    /**
     * check nscm reserve order received
     * @param $intReserveOrderId
     * @return bool
     */
    public function checkPurchaseOrderReceived($intReserveOrderId)
    {
        $strReserveOrderId = strval($intReserveOrderId);
        // check redis
        $objRedis = new Dao_Redis_ReserveOrder();
        $arrRedisOrderInfo = $objRedis->getOrderInfo($strReserveOrderId);
        if (!empty($arrRedisOrderInfo)) {
            return true;
        }
        // check database
        $objDbOrderInfo = Model_Orm_ReserveOrder::getReserveInfoByPurchaseOrderId($intReserveOrderId);
        if (!empty($objDbOrderInfo)) {
            return true;
        }
        return false;
    }

    /**
     * send reserve info to wmq
     * @param $intPurchaseOrderId
     * @return void
     */
    public function sendReserveInfoToWmq($intPurchaseOrderId)
    {
        Dao_Ral_Reserve::writeReserveOrderDb($intPurchaseOrderId);
    }

    /**
     * 查询预约单列表
     *
     * @param $strReserveOrderStatus
     * @param $strWarehouseId
     * @param $strReserveOrderId
     * @param $intVendorId
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     */
    public function getReserveOrderList($strReserveOrderStatus,
                                        $strWarehouseId,
                                        $strReserveOrderId,
                                        $intVendorId,
                                        $arrCreateTime,
                                        $arrOrderPlanTime,
                                        $arrStockinTime,
                                        $intPageNum,
                                        $intPageSize)
    {
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

        $intReserveOrderId = intval(Order_Util::trimReserveOrderIdPrefix($strReserveOrderId));
        $arrReserveOrderStatus = Order_Util::extractIntArray($strReserveOrderStatus);

        // 校验预约单状态参数是否合法
        if (false === Model_Orm_ReserveOrder::isReserveOrderStatusCorrect($arrReserveOrderStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        if(empty($strWarehouseId)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrWarehouseId = Order_Util::extractIntArray($strWarehouseId);

        return Model_Orm_ReserveOrder::getReserveOrderList(
            $arrReserveOrderStatus,
            $arrWarehouseId,
            $intReserveOrderId,
            $intVendorId,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize
        );
    }

    /**
     * 查询预约单状态统计
     *
     * @return array
     */
    public function getReserveOrderStatistics()
    {
        return Model_Orm_ReserveOrder::getReserveOrderStatistics();
    }

    /**
     * 查询预约订单详情
     *
     * @param $strReserveOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function getReserveOrderInfoByReserveOrderId($strReserveOrderId)
    {
        $intReserveOrderId = intval(Order_Util::trimReserveOrderIdPrefix($strReserveOrderId));
        if (empty($intReserveOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        return Model_Orm_ReserveOrder::getReserveOrderInfoByReserveOrderId($intReserveOrderId);
    }

    /**
     * @param $intReserveOrderId
     * @return array
     */
    public function getReserveOrderSkuListAll($intReserveOrderId)
    {
        $intReserveOrderId = intval($intReserveOrderId);
        return Model_Orm_ReserveOrderSku::getReserveOrderSkusByReserveOrderId($intReserveOrderId)['rows'];
    }

    /**
     * 查询采购单商品列表（分页）
     *
     * @param $strReserveOrderId
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     */
    public function getReserveOrderSkuList(
        $strReserveOrderId,
        $intPageNum,
        $intPageSize)
    {
        $intReserveOrderId = intval(Order_Util::trimReserveOrderIdPrefix($strReserveOrderId));

        if (empty($intReserveOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }

        return Model_Orm_ReserveOrderSku::getReserveOrderSkuList($intReserveOrderId, $intPageNum, $intPageSize);
    }

    /**
     * get reserve order info by purchase order id
     * @param int $intPurchaseOrderId
     * @return array
     */
    public function getReserveOrderInfoByPurchaseOrderId($intPurchaseOrderId)
    {
        $arrRet = Model_Orm_ReserveOrder::getReserveInfoByPurchaseOrderId($intPurchaseOrderId);
        if (empty($arrRet)) {
            return [];
        }
        return $arrRet->toArray();
    }

    /**
     * 获取预约入库单打印列表
     * @param $arrOrderIds
     * @return array
     * @throws Order_BusinessError
     */
    public function getReserveOrderPrintList($arrOrderIds)
    {
        if (empty($arrOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $ret = [];
        $arrConditions = $this->getPrintConditions($arrOrderIds);
        $arrColumns = ['reserve_order_status', 'reserve_order_id','purchase_order_id','vendor_name','vendor_id','warehouse_name','reserve_order_remark','warehouse_id','stockin_order_real_amount'];
        $arrRetList = Model_Orm_ReserveOrder::findRows($arrColumns, $arrConditions);
        if (empty($arrRetList)) {
            return $ret;
        }
        $arrWarehouseIds = array_column($arrRetList,'warehouse_id');
        $objDao = new Dao_Ral_Order_Warehouse();
        $arrWarehouseList = $objDao->getWareHouseList($arrWarehouseIds);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $arrSkuColumns = ['reserve_order_id','upc_id','sku_name','sku_net','upc_unit','reserve_order_sku_plan_amount','stockin_order_sku_real_amount','sku_net_unit'];
        $arrReserveSkuList = Model_Orm_ReserveOrderSku::findRows($arrSkuColumns, $arrConditions);
        //$arrReserveSkuList = array_column($arrReserveSkuList,null,'reserve_order_id');
        $arrReserveSkuList = $this->arrayToKeyValue($arrReserveSkuList, 'reserve_order_id');
        foreach ($arrRetList as $key=>$item) {
            $arrRetList[$key]['warehouse_name'] = empty($item['warehouse_name']) ?(isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['warehouse_name']:''):$item['warehouse_name'];
            $arrRetList[$key]['warehouse_contact'] = isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['contact']:'';
            $arrRetList[$key]['warehouse_contact_phone'] = isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['contact_phone']:'';
            $arrRetList[$key]['skus'] = isset($arrReserveSkuList[$item['reserve_order_id']]) ? $arrReserveSkuList[$item['reserve_order_id']]:[];
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

        $arrOrderIds = $this->batchTrimReserveOrderIdPrefix($arrOrderIds);
        // 只查询未软删除的
        $arrConditions = [
            'reserve_order_id' => ['in', $arrOrderIds],
            'is_delete'  => Order_Define_Const::NOT_DELETE,
        ];
        return $arrConditions;
    }

    /**
     * 批次去除预约单开头的ASN开头部分内容
     * @param $arrReserveOrderIds
     */
    private function batchTrimReserveOrderIdPrefix($arrReserveOrderIds)
    {
        foreach ($arrReserveOrderIds as $intKey => $strReserveOrderId) {
            $arrReserveOrderIds[$intKey] = intval(Order_Util::trimReserveOrderIdPrefix($strReserveOrderId));
        }
        return $arrReserveOrderIds;
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
     * @param $intInboundId
     * @param $intStatus
     * @param $intActualTime
     * @param $arrItems
     * @throws Nscm_Exception_Error
     */
    public function syncInboundDirect($intInboundId, $intStatus, $intActualTime, $arrItems)
    {
        Dao_Ral_SyncInbound::syncInboundDirect($intInboundId, $intStatus, $intActualTime, $arrItems);
    }
}
<?php
/**
 * @name Service_Data_Reserve_ReserveOrder
 * @desc Service_Data_Reserve_ReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Reserve_ReserveOrder {

    public function getReserveOrderAndSku($intReserveOrderId)
    {

    }

    /**
     * destroy reserve order
     * @param $intPurchaseOrderId
     * @param $intDestroyType
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
     * create reserve order by nscm reserve order id
     * @param $intPurchaseOrderId
     */
    public function createReserveOrderByPurchaseOrderId($intPurchaseOrderId)
    {
        $objRedis = new Dao_Redis_ReserveOrder();
        $arrOrderInfo = $objRedis->getOrderInfo($intPurchaseOrderId);
        Bd_Log::debug('order info: ' . json_encode($arrOrderInfo));
        if (empty($arrOrderInfo)) {
            // @alarm
            Bd_Log::warning('can`t find nscm purhcase order id: ' . $intPurchaseOrderId);
            return;
        }
        Model_Orm_ReserveOrder::getConnection()->transaction(function() use($arrOrderInfo, $intPurchaseOrderId) {
            $intReserveOrderId = intval($arrOrderInfo['reserve_order_id']);
            $intWarehouseId = intval($arrOrderInfo['warehouse_id']);
            $strWarehouseName = strval($arrOrderInfo['warehouse_name']);
            $intReserveOrderPlanTime = intval($arrOrderInfo['reserve_order_plan_time']);
            $intReserveOrderPlanAmount = intval($arrOrderInfo['reserve_order_plan_amount']);
            $intVendorId = intval($arrOrderInfo['vendor_id']);
            $strVendorName = strval($arrOrderInfo['vendor_name']);
            $strVendorContactor = strval($arrOrderInfo['vendor_contactor']);
            $strVendorMobile = strval($arrOrderInfo['vendor_mobile']);
            $strVendorEmail = strval($arrOrderInfo['vendor_email']);
            $strVendorAddress = strval($arrOrderInfo['vendor_address']);
            $strReserveOrderRemark = strval($arrOrderInfo['reserve_order_remark']);
            Model_Orm_ReserveOrder::createReserveOrder($intReserveOrderId, $intPurchaseOrderId, $intWarehouseId, $strWarehouseName, $intReserveOrderPlanTime,
                $intReserveOrderPlanAmount, $intVendorId, $strVendorName, $strVendorContactor,$strVendorMobile, $strVendorEmail, $strVendorAddress, $strReserveOrderRemark);
            $arrReserveOrderSkus = $arrOrderInfo['reserve_order_skus'];
            Model_Orm_ReserveOrderSku::createReserveOrderSku($arrReserveOrderSkus, $intReserveOrderId);
        });
        $objRedis->dropOrderInfo($intPurchaseOrderId);
    }

    /**
     * generate reserve order id
     * @param int $intPurchaseOrderId
     * @return int
     */
    public function generateReserveOrderId($intPurchaseOrderId)
    {
        if ($this->checkPurchaseOrderReceived($intPurchaseOrderId)) {
            Bd_Log::warning('nscm reserve order has already been received, id: ' . $intPurchaseOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED);
        }
        Bd_Log::trace('generate reserve order id by nscm reserve order id: ' . $intPurchaseOrderId);
        $intReserveOrderId = Order_Util_Util::generateReserveOrderCode();
        Bd_Log::debug(sprintf('generate reserve order id[%s] by nscm reserve order id[%s]', $intReserveOrderId, $intPurchaseOrderId));
        return $intReserveOrderId;
    }

    /**
     * send create reserve order
     * @param $arrReserveOrder
     * @return array
     */
    public function saveCreateReserveOrder($arrReserveOrder)
    {
        $intPurchaseOrderId = intval($arrReserveOrder['reserve_order_id']);
        $intReserveOrderId = $this->generateReserveOrderId($intPurchaseOrderId);
        $arrReserve = [
            'reserve_order_id' => $intReserveOrderId,
            'purchase_order_id' => $intPurchaseOrderId,
            'warehouse_id' => intval($arrReserveOrder['warehouse_id']),
            'warehouse_name' => strval($arrReserveOrder['warehouse_name']),
            'reserve_order_plan_time' => intval($arrReserveOrder['reserve_order_plan_time']),
            'reserve_order_plan_amount' => intval($arrReserveOrder['reserve_order_plan_amount']),
            'vendor_id' => intval($arrReserveOrder['vendor_id']),
            'vendor_name' => strval($arrReserveOrder['vendor_name']),
            'vendor_contactor' => strval($arrReserveOrder['vendor_contactor']),
            'vendor_mobile' => strval($arrReserveOrder['vendor_mobile']),
            'vendor_email' => strval($arrReserveOrder['vendor_email']),
            'reserve_order_remark' => strval($arrReserveOrder['reserve_order_remark']),
            'reserve_order_skus' => $arrReserveOrder['reserve_order_skus'],
        ];
        $objRedis = new Dao_Redis_ReserveOrder();
        $key = $objRedis->setOrderInfo($arrReserve);
        $arrRet = [
            'key' => $key,
            'reserve_order_id' => $intReserveOrderId,
        ];
        return $arrRet;
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
        if (!empty($arrDataInfo)) {
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
        //sync mode
        //@todo need change to wmq
        $objDao = new Dao_Rpc();
        $arrReq = [
            Order_Define_Ral::NWMS_ORDER_CREATE_RESERVE_ORDER_WRITE => [
                'purchase_order_id' => $intPurchaseOrderId,
            ]
        ];
        Bd_Log::debug('rpc call input info: ' . json_encode($arrReq));
        $arrRet = $objDao->getData($arrReq);
        Bd_log::debug('rpc call output info: ' . json_encode($arrRet));
        if (0 != json_decode($arrRet[Order_Define_Ral::NWMS_ORDER_CREATE_RESERVE_ORDER_WRITE])['error_no']) {
            Order_Error::throwException(Order_Error_Code::ERR__RAL_ERROR);
        }
    }
}
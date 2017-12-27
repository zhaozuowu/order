<?php
/**
 * @name Service_Data_Purchase_PurchaseOrder
 * @desc Service_Data_Purchase_PurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Purchase_PurchaseOrder {
    
    /**
     * create purchase order by nscm purchase order id
     * @param $intNscmPurchaseOrderId
     */
    public function createPurchaseOrderByNscmPurchaseOrderId($intNscmPurchaseOrderId)
    {
        $objRedis = new Dao_Redis_PurchaseOrder();
        $arrOrderInfo = $objRedis->getOrderInfo($intNscmPurchaseOrderId);
        Bd_Log::debug('order info: ' . json_encode($arrOrderInfo));
        if (empty($arrOrderInfo)) {
            // @alarm
            Bd_Log::warning('can`t find nscm purhcase order id: ' . $intNscmPurchaseOrderId);
            return;
        }
        Model_Orm_PurchaseOrder::getConnection()->transaction(function() use($arrOrderInfo, $intNscmPurchaseOrderId) {
            $intPurchaseOrderId = intval($arrOrderInfo['purchase_order_id']);
            $intWarehouseId = intval($arrOrderInfo['warehouse_id']);
            $strWarehouseName = strval($arrOrderInfo['warehouse_name']);
            $intPurchaseOrderPlanTime = intval($arrOrderInfo['purchase_order_plan_time']);
            $intPurchaseOrderPlanAmount = intval($arrOrderInfo['purchase_order_plan_amount']);
            $intVendorId = intval($arrOrderInfo['vendor_id']);
            $strVendorName = strval($arrOrderInfo['vendor_name']);
            $strVendorContactor = strval($arrOrderInfo['vendor_contactor']);
            $strVendorMobile = strval($arrOrderInfo['vendor_mobile']);
            $strVendorEmail = strval($arrOrderInfo['vendor_email']);
            $strVendorAddress = strval($arrOrderInfo['vendor_address']);
            $strPurchaseOrderRemark = strval($arrOrderInfo['purchase_order_remark']);
            Model_Orm_PurchaseOrder::createPurchaseOrder($intPurchaseOrderId, $intNscmPurchaseOrderId, $intWarehouseId, $strWarehouseName, $intPurchaseOrderPlanTime,
                $intPurchaseOrderPlanAmount, $intVendorId, $strVendorName, $strVendorContactor,$strVendorMobile, $strVendorEmail, $strVendorAddress, $strPurchaseOrderRemark);
            $arrPurchaseOrderSkus = $arrOrderInfo['purchase_order_skus'];
            Model_Orm_PurchaseOrderSku::createPurchaseOrderSku($arrPurchaseOrderSkus, $intPurchaseOrderId);
        });
        $objRedis->dropOrderInfo($intNscmPurchaseOrderId);
    }

    /**
     * generate purchase order id
     * @param int $intNscmPurchaseOrderId
     * @return int
     */
    public function generatePurchaseOrderId($intNscmPurchaseOrderId)
    {
        if ($this->checkNscmPurchaseOrderReceived($intNscmPurchaseOrderId)) {
            Bd_Log::warning('nscm purchase order has already been received, id: ' . $intNscmPurchaseOrderId);
            Order_BusinessError::throwException(Order_Error_Code::NSCM_PURCHASE_ORDER_HAS_BEEN_RECEIVED);
        }
        Bd_Log::trace('generate purchase order id by nscm purchase order id: ' . $intNscmPurchaseOrderId);
        $intPurchaseOrderId = Order_Util_Util::generatePurchaseOrderCode();
        Bd_Log::debug(sprintf('generate purchase order id[%s] by nscm purchase order id[%s]', $intPurchaseOrderId, $intNscmPurchaseOrderId));
        return $intPurchaseOrderId;
    }

    /**
     * send create purchase order
     * @param $arrPurchaseOrder
     * @return array
     */
    public function saveCreatePurchaseOrder($arrPurchaseOrder)
    {
        $intNscmPurchaseOrderId = intval($arrPurchaseOrder['purchase_order_id']);
        $intPurchaseOrderId = $this->generatePurchaseOrderId($intNscmPurchaseOrderId);
        $arrPurchase = [
            'purchase_order_id' => $intPurchaseOrderId,
            'nscm_purchase_order_id' => $intNscmPurchaseOrderId,
            'warehouse_id' => intval($arrPurchaseOrder['warehouse_id']),
            'warehouse_name' => strval($arrPurchaseOrder['warehouse_name']),
            'purchase_order_plan_time' => intval($arrPurchaseOrder['purchase_order_plan_time']),
            'purchase_order_plan_amount' => intval($arrPurchaseOrder['purchase_order_plan_amount']),
            'vendor_id' => intval($arrPurchaseOrder['vendor_id']),
            'vendor_name' => strval($arrPurchaseOrder['vendor_name']),
            'vendor_contactor' => strval($arrPurchaseOrder['vendor_contactor']),
            'vendor_mobile' => strval($arrPurchaseOrder['vendor_mobile']),
            'vendor_email' => strval($arrPurchaseOrder['vendor_email']),
            'purchase_order_remark' => strval($arrPurchaseOrder['purchase_order_remark']),
            'purchase_order_skus' => $arrPurchaseOrder['purchase_order_skus'],
        ];
        $objRedis = new Dao_Redis_PurchaseOrder();
        $key = $objRedis->setOrderInfo($arrPurchase);
        $arrRet = [
            'key' => $key,
            'purchase_order_id' => $intPurchaseOrderId,
        ];
        return $arrRet;
    }

    /**
     * check nscm purchase order received
     * @param $intPurchaseOrderId
     * @return bool
     */
    public function checkNscmPurchaseOrderReceived($intPurchaseOrderId)
    {
        $strPurchaseOrderId = strval($intPurchaseOrderId);
        // check redis
        $objRedis = new Dao_Redis_PurchaseOrder();
        $arrRedisOrderInfo = $objRedis->getOrderInfo($strPurchaseOrderId);
        if (!empty($arrDataInfo)) {
            return true;
        }
        // check database
        $objDbOrderInfo = Model_Orm_PurchaseOrder::getPurchaseInfoByNscmPurchaseOrderId($intPurchaseOrderId);
        if (!empty($objDbOrderInfo)) {
            return true;
        }
        return false;
    }

    /**
     * send purchase info to wmq
     * @param $intNscmPurchaseOrderId
     * @return void
     */
    public function sendPurchaseInfoToWmq($intNscmPurchaseOrderId)
    {
        //sync mode
        //@todo need change to wmq
        $objDao = new Dao_Rpc();
        $arrReq = [
            Order_Define_Ral::NWMS_ORDER_CREATE_PURCHASE_ORDER_WRITE => [
                'nscm_purchase_order_id' => $intNscmPurchaseOrderId,
            ]
        ];
        Bd_Log::debug('rpc call input info: ' . json_encode($arrReq));
        $arrRet = $objDao->getData($arrReq);
        Bd_log::debug('rpc call output info: ' . json_encode($arrRet));
        if (0 != json_decode($arrRet[Order_Define_Ral::NWMS_ORDER_CREATE_PURCHASE_ORDER_WRITE])['error_no']) {
            Order_Error::throwException(Order_Error_Code::ERR__RAL_ERROR);
        }
    }
}
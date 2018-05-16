<?php
/**
 * @name Dao_Wrpc_Oms
 * @desc interact with oms
 * @author huabang.xue@ele.me
 */
class Dao_Wrpc_Oms
{
    /**
     * wrcp service
     * @var Bd_Wrpc_Client
     */
    private $objWrpcService;

    /**
     * init
     * @param string $strServiceName
     */
    public function __construct($strServiceName)
    {
        $this->objWrpcService = new Bd_Wrpc_Client(Order_Define_Wrpc::OMS_APP_ID,
                                                    Order_Define_Wrpc::OMS_NAMESPACE,
                                                    $strServiceName);
    }

    /**
     * 通知oms确认销退入库结果
     * @param array $arrData
     * @return array
     * @throws Order_BusinessError
     */
    public function confirmStockinOrderToOms($arrData) {
        $strRoutingKey = sprintf("stockin_order_id=%s", $arrData['stockin_order_id']);
        $this->objWrpcService->setMeta(["routing-key"=>$strRoutingKey]);
        $arrParams = ['objData' => $arrData];
        $arrRet = $this->objWrpcService->confirmStockinOrder($arrParams);
        Bd_Log::trace(sprintf("method[%s] confirmStockinOrder[%s]", __METHOD__, json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("notify_oms_confirm_stockin_order_fail, error_no[%s], error_msg[%s]", $arrRet['errno'], $arrRet['errmsg']));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKIN_ORDER_CONFIRM_STOCKIN_TO_OMS_FAIL);
        }
        return $arrRet;
    }


    /**
     * 通知OMS修改出库商品数量
     * @param int   $intLogisticOrderId
     * @param int   $intShipmentOrderId
     * @param array $arrPickupSkuInfoList
     * @return integer
     * @throws Order_BusinessError
     */
    public function updateStockoutOrderSkuInfo($intLogisticOrderId, $intShipmentOrderId, $arrPickupSkuInfoList) {
        $arrParams = [
            'logistic_order_id' => $intLogisticOrderId,
            'shipment_order_id' => $intShipmentOrderId,
            'sku_info' => $arrPickupSkuInfoList,
        ];
        $arrRet = $this->objWrpcService->updateStockoutOrderSkuPickupInfo(['objAcceptedSkuInfo' => $arrParams]);
        Bd_Log::trace(sprintf("method_%s_updateStockoutOrderSkuPickupInfo_params_%s",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s]",__METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_PICKUP_NOTICE_OMS_FAILED);
        }
        return $arrRet['data'];
    }

}
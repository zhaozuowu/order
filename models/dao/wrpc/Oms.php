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
     */
    public function __construct()
    {
        $this->objWrpcService = new Bd_Wrpc_Client(Order_Define_Wrpc::OMS_APP_ID,
                                                    Order_Define_Wrpc::OMS_NAMESPACE,
                                                    Order_Define_Wrpc::OMS_SERVICE_NAME);
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

}
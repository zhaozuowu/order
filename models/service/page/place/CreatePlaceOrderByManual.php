<?php
/**
 * @name CreatePlaceOrderByManual.php
 * @desc CreatePlaceOrderByManual.php
 * @author yu.jin03@ele.me
 */

class Service_Page_Place_CreatePlaceOrderByManual implements Order_Base_Page
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    /**
     * Service_Page_Place_CreatePlaceOrderByManual constructor.
     */
    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return array|void
     * @throws Order_BusinessError
     * @throws Wm_Error
     */
    public function execute($arrInput)
    {
        if (empty($arrInput['stockin_order_ids'])) {
            Order_BusinessError::throwException(Order_Error_Code::CREATE_PLACE_ORDER_PARAMS_ERROR);
        }
        $arrStockinOrderIds = explode(',', $arrInput['stockin_order_ids']);
        foreach ((array)$arrStockinOrderIds as $intKey => $strStockinOrderId) {
            $arrStockinOrderIds[$intKey] = ltrim($strStockinOrderId, Nscm_Define_OrderPrefix::SIO);
        }
        $arrInput['stockin_order_ids'] = implode(',', $arrStockinOrderIds);
        $this->objDsPlaceOrder->checkPlaceOrderExisted($arrInput['stockin_order_ids']);
        $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_PLACE_ORDER_CREATE, $arrInput);
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] send cmd[%s] params[%s] failed",
                            __METHOD__, Order_Define_Cmd::CMD_PLACE_ORDER_CREATE, json_encode($arrInput)));
        }
    }
}
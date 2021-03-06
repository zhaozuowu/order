<?php
/**
 * @name ConfirmPlaceOrder.php
 * @desc ConfirmPlaceOrder.php
 * @author yu.jin03@ele.me
 */

class Service_Page_Place_ConfirmPlaceOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    /**
     * Service_Page_Place_ConfirmPlaceOrder constructor.
     */
    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return array|void
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intPlaceOrderId = $arrInput['place_order_id'];
        $arrSkus = $arrInput['skus'];
        $strUserName = strval($arrInput['_session']['user_name']);
        $intUserId = intval($arrInput['_session']['user_id']);
        Bd_Log::trace(sprintf("method[%s] arrInput[%s]", __METHOD__, json_encode($arrInput)));
        return $this->objDsPlaceOrder->confirmPlaceOrder($intPlaceOrderId, $arrSkus, $strUserName, $intUserId);
    }
}
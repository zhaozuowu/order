<?php
/**
 * @name GetPlaceOrderPrint.php
 * @desc GetPlaceOrderPrint.php
 * @author yu.jin03@ele.me
 */

class Service_Page_Place_GetPlaceOrderPrint implements Order_Base_Page
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    /**
     * Service_Page_Place_GetPlaceOrderPrint constructor.
     */
    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrPlaceOrderIds = explode(',', $arrInput['place_order_ids']);
        return $this->objDsPlaceOrder->getPlaceOrderPrint($arrPlaceOrderIds);
    }
}
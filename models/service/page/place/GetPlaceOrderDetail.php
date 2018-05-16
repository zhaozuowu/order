<?php
/**
 * @name GetPlaceOrderDetail.php
 * @desc GetPlaceOrderDetail.php
 * @author yu.jin03@ele.me
 */

class Service_Page_Place_GetPlaceOrderDetail implements Order_Base_Page
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    public function execute($arrInput)
    {
        return $this->objDsPlaceOrder->getPlaceOrderDetail($arrInput);
    }
}
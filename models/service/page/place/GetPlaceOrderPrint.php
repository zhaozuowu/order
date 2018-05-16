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
     * @param array $arrInput
     * @return array|void
     */
    public function execute($arrInput)
    {
        $arrPlaceOrderIds = json_decode($arrInput['place_order_ids'], true);
        return $this->objDsPlaceOrder->getPlaceOrderPrint($arrPlaceOrderIds);
    }
}
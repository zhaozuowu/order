<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderDetail
 * @desc get pick up order detail
 * @author huabang.xue@ele.me
 */

class Service_Page_Pickup_GetPickupOrderDetail
{
    /**
     * pick up order data service
     * @var Service_Data_Sku
     */
    protected $objPickupOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objPickupOrder = new Service_Data_PickupOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $ret = $this->objPickupOrder->getPickupOrderByPickupOrderId($arrInput['pickup_order_id']);
        return $ret;
    }
}

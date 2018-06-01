<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderPrint
 * @desc Service_Page_Pickup_GetPickupOrderPrint
 * @author bochao.lv@ele.me
 */

class Service_Page_Pickup_GetPickupOrderPrint
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
        $ret = $this->objPickupOrder->getPickupOrderPrintByPickupOrderIds($arrInput['pickup_order_id']);
        //$ret = $this->objPickupOrder->getPickupOrderByPickupOrderId($arrInput['pickup_order_id']);
        return $ret;
    }
}

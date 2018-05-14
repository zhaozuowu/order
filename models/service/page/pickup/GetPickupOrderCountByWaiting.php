<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderCountByWaiting
 * @desc get pick up order count by waiting
 * @author bochao.lv@ele.me
 */

class Service_Page_Pickup_GetPickupOrderCountByWaiting implements Order_Base_Page
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
     * @param $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $arrWarehouseIds = explode(',', $arrInput['warehouse_ids']);
        return $this->objPickupOrder->getCountByWaiting($arrWarehouseIds);
    }
}

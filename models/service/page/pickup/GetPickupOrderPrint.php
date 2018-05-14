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
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $arrPickupOrderId = explode(',', $arrInput['pickup_order_ids']);
        $ret = $this->objPickupOrder->getPickupOrderSkuInfoByPickupOrderIds($arrPickupOrderId);
        return $ret;
    }
}

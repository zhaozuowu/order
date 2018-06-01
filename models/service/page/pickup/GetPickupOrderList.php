<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderList
 * @desc get pick up order list
 * @author wanggang01@iwaimai.baidu.com
 */

class Service_Page_Pickup_GetPickupOrderList
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
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $ret = $this->objPickupOrder->getPickupOrderList(
            $arrInput['warehouse_ids'],
            $arrInput['create_start_time'],
            $arrInput['create_end_time'],
            $arrInput['page_size'],
            $arrInput['page_num'],
            $arrInput['pickup_order_status'],
            Order_Util::trimStockoutOrderIdPrefix($arrInput['stockout_order_id']),
            $arrInput['pickup_order_id'],
            $arrInput['pickup_order_is_print'],
            $arrInput['update_start_time'],
            $arrInput['update_end_time']
        );
        return $ret;
    }
}

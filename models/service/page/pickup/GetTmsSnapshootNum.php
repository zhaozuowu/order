<?php
/**
 * @name Service_Page_Pickup_GetTmsSnapshootNum
 * @desc 获取tms排线号
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Pickup_GetTmsSnapshootNum
{
    /**
     * @var Service_Data_PickupOrder
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
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $pickupOrderId = $arrInput['pickup_order_id'];
        return $this->objPickupOrder->getTmsSnapshootNum($pickupOrderId);
    }
}
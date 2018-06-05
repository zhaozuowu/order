<?php
/**
 * @name Service_Page_Pickup_CancelPickupOrder
 * @desc 取消拣货单
 * @author hang.song02@ele.me
 */

class Service_Page_Pickup_CancelPickupOrder
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
     * @return int
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intPickupOrderId = $arrInput['pickup_order_id'];
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        $ret = $this->objPickupOrder->cancelPickupOrderById($intPickupOrderId, $userId, $userName);
        return $ret;
    }
}
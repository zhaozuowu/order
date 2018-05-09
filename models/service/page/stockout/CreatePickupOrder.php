<?php
/**
 * @name Service_Page_Stockout_CreatePickupOrder
 * @desc 生成拣货单
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Stockout_CreatePickupOrder
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
        $strStockotOrderIds = $arrInput['stockout_order_ids'];
        $arrStockoutOrderIds = explode(',',$strStockotOrderIds);
        $pickupOrderType = $arrInput['pickup_order_type'];
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        return $this->objPickupOrder->createPickupOrder($arrStockoutOrderIds,$pickupOrderType,$userId,$userName);
    }
}
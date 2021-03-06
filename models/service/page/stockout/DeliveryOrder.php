<?php

/**
 * @name Service_Page_Stockout_DeliveryOrder
 * @desc TMS完成揽收
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_DeliveryOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockoutOrder = new Service_Data_StockoutOrder();
    }


    /**
     * execute
     * @param $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $strStockoutOrderId = $arrInput['stockout_order_id'];
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        Bd_Log::debug("DeliveryOrder execute userinfo:".json_encode($arrInput));
        $this->objStockoutOrder->deliveryOrder($strStockoutOrderId,$userId,$userName);
        return [
            'result' => true,
        ];
    }
}
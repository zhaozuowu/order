<?php

/**
 * @name Service_Page_Stockout_DeliveryOrder
 * @desc TMS完成揽收
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_DeliveryOrder
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

        $strStockoutOrderId = isset($arrInput['stockout_order_id']) ? intval($arrInput['stockout_order_id']) : 0;
        return $this->objStockoutOrder->deliveryOrder($strStockoutOrderId);
    }
}
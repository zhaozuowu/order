<?php

/**
 * @name Service_Page_Stockout_GetCustomernameSug
 * @desc 查询客户名称sug
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetCustomernameSug implements Order_Base_Page
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
        $orderType = $arrInput['order_type'];
        return $this->objStockoutOrder->getCustomernameSug($orderType);
    }
}
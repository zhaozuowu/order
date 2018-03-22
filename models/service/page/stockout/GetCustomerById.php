<?php

/**
 * @name Service_Page_Stockout_GetCustomerById
 * @desc 查询客户信息
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetCustomerById implements Order_Base_Page
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
        $customerId = $arrInput['customer_id'];
        return $this->objStockoutOrder->getCustomerInfoById($customerId);
    }
}
<?php

/**
 * @name Service_Page_GetBusinessFormOrderList
 * @desc 查询业态订单列表
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_GetBusinessFormOrderList
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
        return $this->objStockoutOrder->getBusinessFormOrderList($arrInput);
    }
}
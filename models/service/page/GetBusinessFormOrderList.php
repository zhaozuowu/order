<?php

/**
 * @name Service_Page_GetBusinessFormOrderList
 * @desc 查询业态订单列表
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_GetBusinessFormOrderList
{
    /**
     * @var Service_Data_BusinessFormOrder
     */
    protected $objStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockoutOrder = new Service_Data_BusinessFormOrder();
    }


    /**
     * execute
     * @param $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $arrList = $this->objStockoutOrder->getBusinessFormOrderList($arrInput);
        $intTotal = $this->objStockoutOrder->getBusinessFormOrderCount($arrInput);
        return ['total' => $arrList, 'orders' => $arrList];

    }
}
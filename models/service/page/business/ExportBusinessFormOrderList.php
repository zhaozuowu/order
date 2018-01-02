<?php
/**
 * @name Service_Page_Business_ExportBusinessFormOrderList
 * @desc 导出业态订单
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Business_ExportBusinessFormOrderList
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
        return ['total' => $intTotal, 'orders' => $arrList];

    }
}
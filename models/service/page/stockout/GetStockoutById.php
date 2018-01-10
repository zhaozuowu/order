<?php

/**
 * @name Service_Page_Stockout_GetStockoutById
 * @desc 查询出库单明细
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetStockoutById implements Order_Base_Page
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
        return $this->objStockoutOrder->getOrderAndSkuListByStockoutOrderId($strStockoutOrderId);
    }
}
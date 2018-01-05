<?php

/**
 * @name Service_Page_Stockout_GetLogList
 * @desc 查询出库单日志
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetLogList implements Order_Base_Page
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
        return $this->objStockoutOrder->getLogList($strStockoutOrderId);
    }
}
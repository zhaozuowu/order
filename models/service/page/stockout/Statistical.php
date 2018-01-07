<?php

/**
 * @name Service_Page_Stockout_Statistical
 * @desc 出库单状态统计
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_Statistical implements Order_Base_Page
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
        $warehouseIds = $arrInput['warehouse_ids'];
        return $this->objStockoutOrder->getStockoutOrderStatisticalInfo($warehouseIds);
    }
}
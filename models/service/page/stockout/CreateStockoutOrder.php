<?php
/*
 * @file: CreateStockoutOrder.php
 * @Author: jinyu02 
 * @Date: 2017-12-26 15:36:39 
 * @Last Modified by:   jinyu02 
 * @Last Modified time: 2017-12-26 15:36:39 
 */
class Service_Page_Business_CreateStockoutOrder{

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;

    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    public function execute($arrInput) {
        return $objDsStockoutOrder->createStockoutOrder($arrInput);
    }

}

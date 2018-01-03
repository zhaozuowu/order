<?php
/*
 * @file: CreateStockoutOrder.php
 * @Author: jinyu02 
 * @Date: 2017-12-26 15:36:39 
 * @Last Modified by: jinyu02
 * @Last Modified time: 2017-12-27 19:46:55
 */
class Service_Page_Stockout_GetStockoutOrderList {

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;

    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    public function execute($arrInput) {
        return $this->objDsStockoutOrder->getStockoutOrderListAndCount($arrInput);
    }

}

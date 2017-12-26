<?php

/**
 * @name Service_Page_DeliveryOrder
 * @desc TMS完成揽收
 * @author nscm
 */
class Service_Page_DeliveryOrder
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
        return $this->objStockoutOrder->deliveryOrder($arrInput);
    }
}
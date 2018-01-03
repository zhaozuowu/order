<?php

/**
 * @name Service_Page_Order_Commit_Cmdnwmsstockoutorderdelete
 * @desc 作废出库单
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Cmdnwmsstockoutorderdelete extends Wm_Lib_Wmq_CommitPageService
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
     * create stockout order
     * @param array $arrInput
     * @return array
     */
    public function myExecute($arrInput)
    {
        $strStockoutOrderId = $arrInput['stockout_order_id'];
        return $this->objStockoutOrder->deleteStockoutOrder($strStockoutOrderId);
    }


}
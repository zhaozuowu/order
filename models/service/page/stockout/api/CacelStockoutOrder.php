<?php

/**
 * @name Service_Page_Stockout_Api_CacelStockoutOrder
 * @desc 确认取消出库单
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_Api_CacelStockoutOrder implements Order_Base_Page
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
     * @throws Nscm_Exception_Error
     */
    public function execute($arrInput)
    {
        $remark = empty($arrInput['remark']) ? '': $arrInput['remark'];
        $arrRet = $this->objStockoutOrder->confirmCancelStockoutOrder($arrInput['stockout_order_id'],$remark);
        return $arrRet;
    }
}
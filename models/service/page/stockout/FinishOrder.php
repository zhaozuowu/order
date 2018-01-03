<?php
/**
 * @name Service_Page_FinishOrder
 * @desc TMS完成门店签收
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Stockout_FinishOrder
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
        $strStockoutOrderId = isset($arrInput['stockout_order_id']) ? intval($arrInput['stockout_order_id']) : 0;
        $signupStatus = isset($arrInput['signup_status']) ? intval($arrInput['signup_status']) : 0;
        $signupUpcs = isset($arrInput['signup_upcs']) ? json_decode($arrInput['signup_upcs'], true) : [];
        return $this->objStockoutOrder->finishorder($strStockoutOrderId, $signupStatus, $signupUpcs);
    }
}
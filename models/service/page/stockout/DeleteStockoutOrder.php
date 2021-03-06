<?php
/**
 * @name Service_Page_Stockout_DeleteStockoutOrder
 * @desc 作废出库单
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Stockout_DeleteStockoutOrder
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
        $mark= $arrInput['mark'];
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        Bd_Log::debug("DeleteStockoutOrder execute userinfo:".json_encode($arrInput));
        return $this->objStockoutOrder->deleteStockoutOrder($strStockoutOrderId,$mark,$userId,$userName);
    }
}
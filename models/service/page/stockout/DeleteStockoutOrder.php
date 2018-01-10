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
        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId];
        $strCmd = Order_Define_Cmd::CMD_DELETE_STOCKOUT_ORDER;
        $ret =Order_Wmq_Commit::sendCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
        }
        return $ret;

        //return $this->objStockoutOrder->finishPickup($strStockoutOrderId, $pickupSkus);
    }
}
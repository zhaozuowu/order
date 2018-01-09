<?php
/**
 * @name Service_Page_Stockout_FinishPickupOrder
 * @desc 仓库完成拣货
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Stockout_FinishPickupOrder
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
        $pickupSkus = is_array($arrInput['pickup_skus']) ? $arrInput['pickup_skus'] : json_decode($arrInput['pickup_skus'], true);
        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId, 'pickup_skus' => $pickupSkus];
        $strCmd = Order_Define_Cmd::CMD_FINISH_PRICKUP_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL);
        }
        return [];

        //return $this->objStockoutOrder->finishPickup($strStockoutOrderId, $pickupSkus);
    }
}
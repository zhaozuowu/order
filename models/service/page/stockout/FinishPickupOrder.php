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
        $arrWmqConfig = Order_Define_Cmd::DEFAULT_WMQ_CONFIG;
        $arrWmqConfig['Key'] = $strStockoutOrderId;
        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId, 'pickup_skus' => $pickupSkus];
        $strCmd = Order_Define_Cmd::CMD_FINISH_PRICKUP_ORDER;
        $ret = Wm_Lib_Wmq_Commit::sendCmd($strCmd, $arrStockoutParams, $arrWmqConfig);
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
        }
        return $ret;

        //return $this->objStockoutOrder->finishPickup($strStockoutOrderId, $pickupSkus);
    }
}
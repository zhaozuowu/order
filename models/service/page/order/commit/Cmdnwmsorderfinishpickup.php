<?php

/**
 * @name Service_Page_Order_Commit_Cmdnwmsorderfinishpickup
 * @desc 仓库完成拣货
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Cmdnwmsorderfinishpickup extends Wm_Lib_Wmq_CommitPageService
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
        $pickupSkus = is_array($arrInput['pickup_skus']) ? $arrInput['pickup_skus'] : json_decode($arrInput['pickup_skus'], true);
        Bd_Log::debug("notify TMS finishpickup myExecute:".json_encode($arrInput));
        return [];
        //return $this->objStockoutOrder->finishPickup($strStockoutOrderId, $pickupSkus);
    }


}
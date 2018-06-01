<?php

/**
 * @name Service_Page_Order_Commit_Pickuporderfinishstockout
 * @desc 拣货单完成
 * @author hang.song02@ele.me
 */
class Service_Page_Order_Commit_Pickuporderfinishstockout extends Wm_Lib_Wmq_CommitPageService
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
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function myExecute($arrInput)
    {
        $intPickupOrderId = $arrInput['pickup_order_id'];
        $arrPickupSkus = $arrInput['pickup_skus'];
        $userId = $arrInput['user_id'];
        $userName = $arrInput['user_name'];
        $this->objStockoutOrder->batchFinishOrder($intPickupOrderId, $arrPickupSkus, $userId, $userName);
        return [];
    }


}
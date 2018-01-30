<?php

/**
 * @name Service_Page_Order_Commit_Stockoutorderfinishpickup
 * @desc 仓库完成拣货
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Stockoutorderfinishpickup extends Wm_Lib_Wmq_CommitPageService
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
        $intShipmentOrderId = $arrInput['shipment_order_id'];
        $intStockoutOrderId = $arrInput['stockout_order_id'];
        $arrPickupSkus = is_array($arrInput['pickup_skus']) ? $arrInput['pickup_skus'] : json_decode($arrInput['pickup_skus'], true);
        Bd_Log::debug("notify TMS finishpickup myExecute:".json_encode($arrInput));
        $this->objStockoutOrder->syncNotifyTmsFinishPickup($intStockoutOrderId, $intShipmentOrderId, $arrPickupSkus);
        return [];
    }


}
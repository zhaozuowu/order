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
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strStockoutOrderId = $arrInput['stockout_order_id'];
        $arrPickupSkus = is_array($arrInput['pickup_skus']) ? $arrInput['pickup_skus'] : json_decode($arrInput['pickup_skus'], true);
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        return $this->objStockoutOrder->finishPickup($strStockoutOrderId, $arrPickupSkus, $userId, $userName);
        //$this->objStockoutOrder->syncNotifyTmsFinishPickup('121801220001201', $arrPickupSkus);
    }
}
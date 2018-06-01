<?php
/**
 * @name Service_Page_Stockout_BatchFinishPickupOrder
 * @desc 仓库批量完成拣货
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Page_Stockout_BatchFinishPickupOrder
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
        Order_Error::throwException(Order_Error_Code::INTERFACE_HAS_BEEN_DISCARDED);
        $strStockoutOrderIds = $arrInput['stockout_order_ids'];
        $arrStockoutOrderIds = explode(',',$strStockoutOrderIds);
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        Bd_Log::debug("batchfinishPickupOrder execute userinfo:".json_encode($arrInput));
        return $this->objStockoutOrder->batchFinishPickup($arrStockoutOrderIds,$userId, $userName);
    }
}
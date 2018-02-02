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
        $strStockoutOrderId = isset($arrInput['stockout_order_id']) ? $arrInput['stockout_order_id'] : '';
        $intSignupStatus = isset($arrInput['signup_status']) ? intval($arrInput['signup_status']) : 0;
        $arrSignupSkus = isset($arrInput['signup_skus']) ? $arrInput['signup_skus'] : [];
        $arrSignupSkus = is_array($arrSignupSkus) ? $arrSignupSkus:json_decode($arrInput['signup_skus'], true);
        $rs = $this->objStockoutOrder->finishorder($strStockoutOrderId, $intSignupStatus, $arrSignupSkus);
        return [
            'result' => true,
        ];
    }
}
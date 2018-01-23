<?php
/**
 * @name Service_Page_Stockout_GetCancelStatus
 * @desc 查询出库单取消状态
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetCancelStatus implements Order_Base_Page
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
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intCancelStatus = $this->objStockoutOrder->getCancelStatus($arrInput);
        return [
            'is_cancelled' => $intCancelStatus,
        ];
    }
}
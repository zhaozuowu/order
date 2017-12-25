<?php

/**
 * @name Service_Data_StockoutOrder
 * @desc 出库订单操作类
 * @author zhaozuowu@iwaimai.baidu.com　
 */
class Service_Data_StockoutOrder
{
    protected $objOrmStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {

    }

    public function getStockoutOrderInfoById($stockoutOrderId)
    {
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return [];
        }

    }
}
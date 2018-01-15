<?php
/**
 * @name Service_Page_Stockout_GetStockoutOrderSkus
 * @desc 查询出库单商品列表
 * @author jinyu02@iwaimai.baidu.com
 */

class Service_Page_Stockout_GetStockoutOrderSkus
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
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrList = $this->objStockoutOrder->getStockoutOrderSkus($arrInput);
        $intTotal = $this->objStockoutOrder->getStockoutOrderSkusCount($arrInput);
        return [
            'total' => $intTotal,
            'skus' => $arrList,
        ];
    }

}
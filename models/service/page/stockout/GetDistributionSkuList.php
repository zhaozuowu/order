<?php
/**
 * @name Service_Page_Stockout_GetDistributionSkuList
 * @desc 查询配货商品列表
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetDistributionSkuList {

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;

    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    public function execute($arrInput) {
        $arr= $this->objDsStockoutOrder->getDistributionSkuList($arrInput);
        return $arr;
    }

}

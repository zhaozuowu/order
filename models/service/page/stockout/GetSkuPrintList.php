<?php
/**
 * @name Service_Page_Order_GetOrderPrintList
 * @desc 查询打印列表
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetSkuPrintList {

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;
    protected $objDsStockintOrder;

    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    public function execute($arrInput) {
        $arrOrderIds = explode(',', $arrInput['order_ids']);
        return $this->objDsStockoutOrder->getSkuPrintList($arrOrderIds);
    }

}

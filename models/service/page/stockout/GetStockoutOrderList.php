<?php
/**
 * @name Service_Page_Stockout_GetStockoutOrderList
 * @desc 查询出库单分页列表
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetStockoutOrderList {

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;

    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    public function execute($arrInput) {
        $arrRetList = $this->objDsStockoutOrder->getStockoutOrderList($arrInput);
        $intCount = $this->objDsStockoutOrder->getStockoutOrderCount($arrInput);
        return [
            'total' => $intCount,
            'orders' => $arrRetList,
        ];
    }

}

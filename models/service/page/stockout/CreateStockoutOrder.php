<?php
/**
 * @name Service_Page_Stockout_CreateStockoutOrder
 * @desc 创建出库单
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Stockout_CreateStockoutOrder{

    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objDsStockoutOrder;

    /**
     * init
     */
    public function __construct() {
        $this->objDsStockoutOrder = new Service_Data_StockoutOrder();
    }

    /**
     * create stockout order
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput) {
        if (empty($arrInput['stockout_order_id'])) {
            $arrInput['stockout_order_id'] = Order_Util_Util::generateStockoutOrderId();
        }
        return $this->objDsStockoutOrder->createStockoutOrder($arrInput);
    }

}

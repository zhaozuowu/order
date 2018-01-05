<?php
/**
 * @name Service_Page_Order_Commit_Cmdnwmsorderstockoutcreate
 * @desc 异步创建出库单
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Cmdnwmsorderstockoutcreate extends Wm_Lib_Wmq_CommitPageService {
    
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
    public function myExecute($arrInput) {
        Bd_Log::trace(sprintf("method[%s] arrInput[%s]", __METHOD__, json_encode($arrInput)));
        return $this->objDsStockoutOrder->createStockoutOrder($arrInput);
    }
}
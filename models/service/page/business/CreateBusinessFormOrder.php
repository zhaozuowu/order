<?php

/**
 * @name Service_Page_Business_CreateBusinessFormOrder
 * @desc 创建业态订单
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Business_CreateBusinessFormOrder {
    
    /**
     * @var Service_Data_BusinessFormOrder
     */
    private $objDsBusinessFormOrder;

    /**
     * @var Service_Data_StockoutOrder
     */
    private $objDsStockoutFormOrder;
    /**
     * init
     */
    public function __construct() {
        $this->objDsBusinessFormOrder = new Service_Data_BusinessFormOrder();
        $this->objDsStockoutFormOrder = new Service_Data_StockoutOrder();
    }
    
    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput) {
        //同步创建业态订单
        $intBusinessFormOrderId = $this->objDsBusinessFormOrder->createBusinessFormOrder($arrInput);
        //异步创建出库单
        $this->objDsStockoutFormOrder->assembleStockoutOrder($arrInput, $intBusinessFormOrderId);
        $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER, $arrInput,
                                            strval($arrInput['stockout_order_id']));
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error",
                                    __METHOD__, Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER));
        }
        return $ret;
    }
}
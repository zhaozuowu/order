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
     * @var Service_Data_Sku
     */
    private $objDsSku;
    /**
     * init
     */
    public function __construct() {
        $this->objDsBusinessFormOrder = new Service_Data_BusinessFormOrder();
        $this->objDsStockoutFormOrder = new Service_Data_StockoutOrder();
        $this->objDsSku = new Service_Data_Sku();
    }

    /**
     * @param array $arrInput
     * @return int
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function execute($arrInput) {
        //同步创建业态订单
        $arrInput['skus'] = $this->objDsSku->appendSkuInfosToSkuParams($arrInput['skus']);
        $arrInput = $this->objDsStockoutFormOrder->assembleStockoutOrder($arrInput);
        $arrInput = $this->objDsBusinessFormOrder->createBusinessFormOrder($arrInput);
        $this->objDsStockoutFormOrder->createStockoutOrder($arrInput);
        return true;
        //异步创建出库单
        $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER, $arrInput,
                                            strval($arrInput['stockout_order_id']));
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error",
                                    __METHOD__, Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER));
        }
        return $ret;
    }
}
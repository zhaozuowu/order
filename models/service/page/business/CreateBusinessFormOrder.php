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
     * init
     */
    public function __construct() {
        $this->objDsBusinessFormOrder = new Service_Data_BusinessFormOrder();
    }
    
    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput) {
        $this->objDsBusinessFormOrder->createBusinessFormOrder($arrInput);
        //发送订单创建命令
        $arrStockoutParams = $this->objDsBusinessFormOrder->getStockoutCreateParams($arrInput);
        Order_Wmq_Commit::sendCmd(Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER, $arrStockoutParams);
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
        }
        return $ret;
    }
}
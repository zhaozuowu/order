<?php
/**
 * @name Service_Page_Stockin_GetStockinOrderPrintList
 * @desc 入库单打印
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Stockin_GetStockinOrderPrintList {

    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    protected $objData;

    public function __construct() {
        $this->objData = new Service_Data_Stockin_StockinOrder();
    }

    public function execute($arrInput) {
        $arrOrderIds = explode(',', $arrInput['order_ids']);
        return $this->objData->getStockinOrderPrintList($arrOrderIds);
    }

}

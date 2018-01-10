<?php
/**
 * @name Service_Page_Stockout_GetStockoutDetail
 * @desc 销售出库明细
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Page_Stockout_GetStockoutDetail {

    /**
     * @var Service_Data_StockoutDetail
     */
    protected $objData;

    public function __construct() {
        $this->objData = new Service_Data_StockoutDetail();
    }

    public function execute($arrInput) {
        $arrRetList = $this->objData->getStockoutDetail($arrInput);
        $intCount = $this->objData->getStockoutDetailCount($arrInput);
        return [
            'total' => $intCount,
            'list' => $arrRetList,
        ];
    }

}

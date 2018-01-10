<?php
/**
 * @name Service_Page_Reserve_GetReserveOrderPrintList
 * @desc 预约入库单打印
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Reserve_GetReserveOrderPrintList {

    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    protected $objData;

    public function __construct() {
        $this->objData = new Service_Data_Reserve_ReserveOrder();
    }

    public function execute($arrInput) {
        $arrOrderIds = explode(',', $arrInput['order_ids']);
        return $this->objData->getReserveOrderPrintList($arrOrderIds);
    }

}

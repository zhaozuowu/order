<?php
/**
 * @name Service_Page_Reserve_CreateReserveOrderWrite
 * @desc Service_Page_Reserve_CreateReserveOrderWrite
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Reserve_CreateReserveOrderWrite implements Order_Base_Page
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataReserve;

    /**
     * Service_Page_Reserve_CreateReserveOrder constructor.
     */
    function __construct()
    {
        $this->objDataReserve = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $intPurchaseOrderId = intval($arrInput['purchase_order_id']);
        $this->objDataReserve->createReserveOrderByPurchaseOrderId($intPurchaseOrderId);
        return [];
    }
}
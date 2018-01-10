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
    private $objDataPurchase;

    /**
     * Service_Page_Purchase_CreatePurchaseOrder constructor.
     */
    function __construct()
    {
        $this->objDataPurchase = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Exception
     * @throws Wm_Orm_Error
     */
    public function execute($arrInput)
    {
        $intPurchaseOrderId = intval($arrInput['purchase_order_id']);
        $this->objDataPurchase->createReserveOrderByPurchaseOrderId($intPurchaseOrderId);
        return [];
    }
}
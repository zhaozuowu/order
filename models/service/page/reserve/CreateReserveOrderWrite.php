<?php
/**
 * @name Service_Page_Purchase_CreatePurchaseOrderWrite
 * @desc Service_Page_Purchase_CreatePurchaseOrderWrite
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Purchase_CreatePurchaseOrderWrite implements Order_Base_Page
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
     */
    public function execute($arrInput)
    {
        $intNscmPurchaseOrderId = intval($arrInput['nscm_purchase_order_id']);
        $this->objDataPurchase->createPurchaseOrderByNscmPurchaseOrderId($intNscmPurchaseOrderId);
        return [];
    }
}
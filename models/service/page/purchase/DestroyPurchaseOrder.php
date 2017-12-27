<?php
/**
 * @name Service_Page_Purchase_DestroyPurchaseOrder
 * @desc Service_Page_Purchase_DestroyPurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Purchase_DestroyPurchaseOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_Purchase_PurchaseOrder
     */
    private $objDataPurchase;

    /**
     * Service_Page_Purchase_CreatePurchaseOrder constructor.
     */
    function __construct()
    {
        $this->objDataPurchase = new Service_Data_Purchase_PurchaseOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $intNscmPurchaseOrderId = intval($arrInput['purchase_order_id']);
        $intDestroyType = intval($arrInput['destroy_type']);
        $this->objDataPurchase->destroyPurchaseOrder($intNscmPurchaseOrderId, $intDestroyType);
        return [];
    }
}
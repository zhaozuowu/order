<?php
/**
 * @name Service_Page_Reserve_DestroyReserveOrder
 * @desc Service_Page_Purchase_DestroyPurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Reserve_DestroyReserveOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataReserve;

    /**
     * Service_Page_Purchase_CreatePurchaseOrder constructor.
     */
    function __construct()
    {
        $this->objDataReserve = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * do execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intNscmPurchaseOrderId = intval($arrInput['purchase_order_id']);
        $intDestroyType = intval($arrInput['destroy_type']);
        $this->objDataReserve->destroyReserveOrder($intNscmPurchaseOrderId, $intDestroyType);
        return [];
    }
}
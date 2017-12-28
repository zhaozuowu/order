<?php
/**
 * @name Service_Page_Reserve_DestroyReserveOrder
 * @desc Service_Page_Reserve_DestroyReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Reserve_DestroyReserveOrder implements Order_Base_Page
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
        $intPurchaseOrderId = intval($arrInput['reserve_order_id']);
        $intDestroyType = intval($arrInput['destroy_type']);
        $this->objDataReserve->destroyReserveOrder($intPurchaseOrderId, $intDestroyType);
        return [];
    }
}
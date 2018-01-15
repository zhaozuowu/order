<?php
/**
 * @name Service_Page_Reserve_GetReserveOrderStockingCount
 * @desc Service_Page_Reserve_GetReserveOrderStockingCount
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Reserve_GetReserveOrderStockingCount implements Order_Base_Page
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
     * execute
     * @param array $arrInput
     * @return int
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrWarehouseIds = explode(',', $arrInput['warehouse_ids']);
        $intCount = $this->objDataReserve->getOrderStockingCount($arrWarehouseIds);
        return $intCount;
    }
}
<?php
/**
 * @name Service_Page_Place_SugStorageLocation
 * @desc Service_Page_Place_SugStorageLocation
 * @author huabang.xue@ele.me
 */

class Service_Page_Place_SugStorageLocation implements Order_Base_Page
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    /**
     * Service_Page_Place_SugStorageLocation constructor.
     */
    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $intWarehouseId = $arrInput['warehouse_id'];
        $strLocationCode = $arrInput['location_code'];
        $intIsDefaultStore = $arrInput['is_default_store'];
        return $this->objDsPlaceOrder->sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefaultStore);
    }
}
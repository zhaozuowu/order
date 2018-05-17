<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderSkuLocation
 * @desc 获取sku所有库区库位
 * @author hang.song02@ele.me
 */

class Service_Page_Pickup_GetPickupOrderSkuLocation
{
    /**
    * pick up order data service
    * @var Service_Data_Sku
    */
    protected $objPickupOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objPickupOrder = new Service_Data_PickupOrder();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return int
     * @throws Order_BusinessError
     * @throws Nscm_Exception_Error
     */
    public function execute($arrInput)
    {
        $intPickupOrderId = $arrInput['pickup_order_id'];
        $intSkuId = $arrInput['sku_id'];
        $strLocationCode = $arrInput['location_code'];
        $intExpireTime = $arrInput['expire_time'];
        $ret = $this->objPickupOrder->getSkuLocation($intPickupOrderId, $intSkuId, $strLocationCode, $intExpireTime);
        return $ret;
    }
}
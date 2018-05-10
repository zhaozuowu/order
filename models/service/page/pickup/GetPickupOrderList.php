<?php
/**
 * @name Service_Page_Pickup_GetPickupOrderList
 * @desc get pick up order list
 * @author wanggang01@iwaimai.baidu.com
 */

class Service_Page_Pickup_GetPickupOrderList
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
     * @return array
     */
    public function execute($arrInput)
    {
        $ret = [];
//        $ret = $this->objSku->($arrInput['page_size'],
//            $arrInput['sku_id'],
//            $arrInput['upc_id'],
//            $arrInput['sku_name'],
//            $arrInput['sku_category_1'],
//            $arrInput['sku_category_2'],
//            $arrInput['sku_category_3'],
//            $arrInput['page_num']);
        return $ret;
    }
}

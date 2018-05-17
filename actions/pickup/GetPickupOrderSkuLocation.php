<?php
/**
 * @name Action_Pickup_GetPickupOrderSkuLocation
 * @desc 通过sku获取库区库位
 * @author hang.song02@ele.me
 */

class Action_GetPickupOrderSkuLocation extends Order_Base_Action
{
    /**
     * 需要验证的请求参数
     * @var array
     */
    protected $arrInputParams = [
        'pickup_order_id' => 'int|required',
        'sku_id' => 'int|required',
        'location_code' => 'str',
        'expire_time' => 'int',
    ];
    /**
     * request Method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    public function format($data)
    {
        return $data;
    }

    function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderSkuLocation();
    }

}
<?php
/**
 * @name Action_CreateBusinessFormOrder
 * @desc Action_CreateBusinessFormOrder
 * @author jinyu02@iwaimai.baidu.com
 */

class Action_CreateBusinessFormOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'business_form_order_type' => 'int|required',
        'order_supply_type' => 'int|required',
        'business_form_order_price' => 'int|required',
        'business_form_order_remark' => 'str|required',
        'warehouse_id' => 'int|required',
        'custoner_id' => 'str|required',
        'customer_contactor' => 'str|required',
        'customer_contact' => 'str|required',
        'customer_address' => 'str|required',
        'customer_location' => 'str|required',
        'customer_location_source' => 'str|required',
        'customer_city_id' => 'int|required',
        'customer_city_name' => 'str|required',
        'skus' => [
           'validate' => 'json|decode|decode',
           'type' => 'array',
           'params' => [
                'sku_id' => 'int|required',
                'upc_id' => 'int|required',
                'order_amount' => 'int|required',
                'display_type' => 'int|required',
                'display_floor' => 'int|required',
           ],
        ],
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Business_CreateBusinessFormOrder();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}

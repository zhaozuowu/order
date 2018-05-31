<?php
/**
 * @name Action_CreateReserveOrder
 * @desc Action_CreateReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_Service_CreateReserveOrderService extends Order_Base_ServiceAction
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'purchase_order_id' => 'int|required',
        'warehouse_id' => 'int|required',
        'warehouse_name' => 'strutf8|required|min[1]|len[16]',
        'purchase_order_plan_time' => 'int|required',
        'purchase_order_plan_amount' => 'int|required',
        'vendor_id' => 'int|required',
        'vendor_name' => 'strutf8|required|min[1]|len[32]',
//        'vendor_contactor' => 'strutf8|required|min[1]|max[64]',
//        'vendor_mobile' => 'phone|required',
//        'vendor_email' => 'str|required',
//        'vendor_address' => 'str|required',
        'purchase_order_remark' => 'strutf8',
        'purchase_order_skus' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'upc_id' => 'str|requried|max[64]',
                'upc_unit' => 'int|required|min[1]|max[12]',
                'upc_unit_num' => 'int|required|min[1]',
                'sku_price' => 'int|required|min[0]',
                'sku_price_tax'=> 'int|required|min[0]',
                'reserve_order_sku_total_price' => 'int|required',
                'reserve_order_sku_total_price_tax' => 'int|required',
                'reserve_order_sku_plan_amount' => 'int|required',
            ],
        ],
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Reserve_CreateReserveOrder();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
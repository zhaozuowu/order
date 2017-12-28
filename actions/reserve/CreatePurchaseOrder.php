<?php
/**
 * @name Action_CreatePurchaseOrder
 * @desc Action_CreatePurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_CreatePurchaseOrder extends Order_Base_Action
{
    /**
     * 是否验证登陆
     * @var boolean
     */
    protected $boolCheckLogin = false;

    /**
     * 判断是否有权限
     *
     * @var boolean
     */
    protected $boolCheckAuth = false;

    /**
     * 是否校内网IP
     *
     * @var boolean
     */
    protected $boolCheckIp = true;

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
        'vendor_contactor' => 'strutf8|required|min[1]|max[64]',
        'vendor_mobile' => 'phone|required',
        'vendor_email' => 'str|required',
        'purchase_order_remark' => 'strutf8',
        'purchase_order_skus' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'upc_id' => 'str|requried|max[64]',
                'upc_unit' => 'int|required',
                'upc_unit_num' => 'int|required',
                'sku_name' => 'str|requried|len[64]',
                'sku_net' => 'str|required|len[16]',
                'sku_net_unit' => 'int|required',
                'sku_net_gram' => 'str|required|len[16]',
                'sku_price' => 'int|required',
                'sku_price_tax'=> 'int|required',
                'sku_effect_type' => 'int|required',
                'sku_effect_day' => 'int|required',
                'purchase_order_sku_total_price' => 'int|required',
                'purchase_order_sku_total_price_tax' => 'int|required',
                'purchase_order_sku_plan_amount' => 'int|required',
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
        $this->objPage = new Service_Page_Purchase_CreatePurchaseOrder();
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
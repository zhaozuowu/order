<?php
/**
 * @name Action_CreateStockoutOrder
 * @desc 创建出库单
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_CreateStockoutOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'int',
        'stockout_order_type' => 'int|required',
        'warehouse_id' => 'str|required',
        'stockout_order_remark' => 'str|required',
        'customer_id' => 'int|required',
        'customer_name' => 'str|required',
        'customer_contactor' => 'str|required',
        'customer_contact' => 'str|required',
        'customer_address' => 'str|required',
        'skus' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'upc_id' => 'str|required',
                'order_amount' => 'int|required',
            ],
        ],
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_CreateStockoutOrder
     */
    private $objCreateStockoutOrder;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_CreateStockoutOrder();
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
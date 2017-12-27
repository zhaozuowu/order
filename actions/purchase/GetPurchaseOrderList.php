<?php
/**
 * @name Action_GetPurchaseOrderList
 * @desc Action_GetPurchaseOrderList
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetPurchaseOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'purchase_order_status' => 'str|default[10,20,30,31]',
        'warehouse_id' => 'idlist',
        'purchase_order_id' => 'int|min[0]',
        'vendor_id' => 'int|min[0]',
        'create_time_start' => 'int|min[0]',
        'create_time_end' => 'int|min[0]',
        'purchase_order_plan_time_start' => 'int|min[0]',
        'purchase_order_plan_time_end' => 'int|min[0]',
        'stockin_time_start' => 'int|min[0]',
        'stockin_time_end' => 'int|min[0]',
        'page_num' => 'int|default[1]|min[1]',
        'page_size' => 'int|required|min[1]|max[100]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct function
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Purchase_GetPurchaseOrderList();
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
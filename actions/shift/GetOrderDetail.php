<?php
/**
 * @name Action_GetOrderDetail
 * @desc 获取采购单详情
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'          => 'int|optional',
        'shift_order_id'        => 'regex|patern[/^(S\d{13})?$/]',
        'page_num'              => 'int|optional|default[1]',
        'page_size'             => 'int|optional|default[50]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_Adjust_GetOrderDetail
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_GetOrderDetail();
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
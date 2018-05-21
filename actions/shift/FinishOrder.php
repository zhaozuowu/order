<?php
/**
 * @name Action_Finishorder
 * @desc 创建库存调整单-调增
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_FinishOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'shift_order_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_Adjust_CreateOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_FinishOrder();
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
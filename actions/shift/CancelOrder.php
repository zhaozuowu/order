<?php
/**
 * @name Action_Createiorder
 * @desc 创建移位单
 * @author songwenkai@iwaimai.baidu.com
 */

class Action_CancelOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'shift_order_id' => 'str|required|min[1]|len[64]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * @var
     */
    protected $objPage;

    /**
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_CancelOrder();
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
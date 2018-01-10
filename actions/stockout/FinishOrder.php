<?php
/**
 * @name Action_DeliveryOrder
 * @desc TMS完成揽收
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_FinishOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
        'signup_status' => 'int|required',
        'signup_skus' => 'str',

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
        $this->objPage = new Service_Page_Stockout_FinishOrder();
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
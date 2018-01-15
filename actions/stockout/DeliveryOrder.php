<?php
/**
 * @name Action_DeliveryOrder
 * @desc TMS完成揽收
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_DeliveryOrder extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
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


        $this->objPage = new Service_Page_Stockout_DeliveryOrder();
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
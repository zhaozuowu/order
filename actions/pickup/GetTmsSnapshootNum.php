<?php
/**
 * @name Action_CreatePickupOrder
 * @desc 获取tms排线信息
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetTmsSnapshootNum extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'pickup_order_id' => 'int|required',
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
        $this->objPage = new Service_Page_Pickup_GetTmsSnapshootNum();

        
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
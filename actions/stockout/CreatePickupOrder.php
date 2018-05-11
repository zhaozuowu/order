<?php
/**
 * @name Action_CreatePickupOrder
 * @desc 生成拣货单
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_CreatePickupOrder extends Order_Base_Action
{
    protected $boolCheckLogin = false;
    protected  $boolCheckAuth = false;
    protected  $boolCheckIp = false;


    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_ids' => 'str|required',
        'pickup_order_type' => 'int|required',
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
        $this->objPage = new Service_Page_Stockout_CreatePickupOrder();

        
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
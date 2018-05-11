<?php
/**
 * @name Action_GetPickupRowsPrintList
 * @desc 拣货单排线打印
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetPickupRowsPrintList extends Order_Base_Action
{
    protected $boolCheckLogin = false;
    protected  $boolCheckAuth = false;
    protected  $boolCheckIp = false;


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
        $this->objPage = new Service_Page_Pickup_GetPickupRowsPrintList();

        
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
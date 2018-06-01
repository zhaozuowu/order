<?php
/**
 * @name Action_CreatePickupOrder
 * @desc 生成拣货单
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_CreatePickupOrder extends Order_Base_Action
{

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
        $this->objPage = new Service_Page_Pickup_CreatePickupOrder();

        
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

    /**
     * 添加参数校验规则，在需要的时候，进行重写
     * @return array
     */
    public function addCheckParam()
    {
        return [
          'pickup_order_type' =>$this->arrReqPost['pickup_order_type'],
        ];
    }

}
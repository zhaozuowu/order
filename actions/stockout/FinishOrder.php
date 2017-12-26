<?php
/**
 * @name Action_DeliveryOrder
 * @desc TMS完成揽收
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_FinishOrder extends Order_Base_Action
{
    protected $boolCheckLogin = false;
    protected $boolCheckAuth = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'int|required',
        'signup_status' => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_DeliveryOrder
     */
    private $objDeliveryOrder;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objDeliveryOrder = new Service_Page_DeliveryOrder();
    }

    /**
     * execute
     * @return array
     */
    public function myExecute()
    {
        return $this->objDeliveryOrder->execute($this->arrFilterResult);
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
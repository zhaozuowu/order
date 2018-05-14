<?php
/**
 * @name Action_Pickup_CancelPickupOrder
 * @desc 取消拣货单
 * @author hang.song02@ele.me
 */

class Action_CancelPickupOrder extends Order_Base_Action
{
    protected $arrInputParams = [
        'pickup_order_id' => 'int|required',
    ];
    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * 格式化输出
     *
     * @param  array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }

    /**
     * constructor
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_CancelPickupOrder();
    }
}
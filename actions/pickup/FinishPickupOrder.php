<?php
/**
 * @name Action_Pickup_FinishPickupOrder
 * @desc 拣货完成
 * @author hang.song02@ele.me
 */

class Action_Pickup_FinishPickupOrder extends Order_Base_Action
{
    protected $arrInputParams = [
        'pickup_order_id' => 'int|required',
        'pickup_skus' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'pickup_amount' => 'int|required',
                'pickup_extra_info' => 'json|required|decode',
            ],
        ],
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
        $this->objPage = new Service_Page_Pickup_FinishPickupOrder();
    }
}
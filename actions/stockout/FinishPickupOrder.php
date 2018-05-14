<?php
/**
 * @name Action_FinishPickupOrder
 * @desc 仓库完成拣货
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_FinishPickupOrder extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
        'pickup_skus' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'pickup_amount' => 'int|required',
            ],
        ],
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
        Order_Error::throwException(Order_Error_Code::INTERFACE_HAS_BEEN_DISCARDED);
        $this->objPage = new Service_Page_Stockout_FinishPickupOrder();

        
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
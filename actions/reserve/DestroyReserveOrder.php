<?php
/**
 * @name Action_DestroyReserveOrder
 * @desc Action_DestroyReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_DestroyReserveOrder extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'purchase_order_id' => 'int|required',
        'destroy_type' => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Reserve_DestroyReserveOrder();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
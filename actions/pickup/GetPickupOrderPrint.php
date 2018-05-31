<?php
/**
 * @name GetPickupOrderPrint.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/12 16:23
 */

class Action_GetPickupOrderPrint extends Order_Base_Action
{
    /**
     * params
     * @var array
     */
    protected $arrInputParams = [
        'pickup_order_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * constructor
     * @return mixed
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderPrint();
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
<?php
/**
 * Class Action_FinishOrder
 */

class Action_FinishOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'shift_order_id' => 'str|required|min[1]|len[64]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * @var
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_FinishOrder();
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
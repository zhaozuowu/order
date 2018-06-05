<?php
/**
 * Class Action_CancelOrder
 */

class Action_CancelOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'shift_order_id' => 'regex|patern[/^(M\d{13})?$/]',
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
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_CancelOrder();
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
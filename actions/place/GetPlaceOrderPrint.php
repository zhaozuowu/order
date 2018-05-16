<?php
/**
 * @name GetPlaceOrderPrint.php
 * @desc GetPlaceOrderPrint.php
 * @author yu.jin03@ele.me
 */
class Action_GetPlaceOrderPrint extends Order_Base_Action
{
    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_ids' => 'str|required',
    ];

    /**
     *
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_GetPlaceOrderPrint();
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
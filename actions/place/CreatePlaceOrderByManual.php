<?php
/**
 * @name CreatePlaceOrderByManual.php
 * @desc CreatePlaceOrderByManual.php
 * @author yu.jin03@ele.me
 */
class Action_CreatePlaceOrderByManual extends Order_Base_Action
{
    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_ids' => 'str|required',
    ];

    /**
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_CreatePlaceOrderByManual();
    }

    /**
     * format data
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
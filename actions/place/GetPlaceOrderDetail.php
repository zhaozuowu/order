<?php
/**
 * @name GetPlaceOrderDetail.php
 * @desc GetPlaceOrderDetail.php
 * @author yu.jin03@ele.me
 */
class Action_GetPlaceOrderDetail extends Order_Base_Action
{
    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * init object
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_GetPlaceOrderDetail();
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
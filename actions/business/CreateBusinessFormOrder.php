<?php
/**
 * @name Action_CreateBusinessFormOrder
 * @desc Action_CreateBusinessFormOrder
 * @author jinyu02@iwaimai.baidu.com
 */

class Action_CreateBusinessFormOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'param1' => 'int|required',
        'param2' => 'int|required',
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
        $this->objPage = new Service_Page_Business_CreateBusinessForm();
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

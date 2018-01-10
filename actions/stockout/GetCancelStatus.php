<?php
/**
 * @name Action_GetCancelStatus
 * @desc 查询出库单取消状态
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_GetCancelStatus extends Order_Base_Action
{
    protected $boolCheckAuth = false;
    protected $boolCheckLogin = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_GetCancelStatus();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($arrRet)
    {
        return $arrRet;
    }

}
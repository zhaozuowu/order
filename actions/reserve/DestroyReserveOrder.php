<?php
/**
 * @name Action_DestroyReserveOrder
 * @desc Action_DestroyReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_DestroyReserveOrder extends Order_Base_Action
{
    /**
     * 是否验证登陆
     * @var boolean
     */
    protected $boolCheckLogin = false;

    /**
     * 判断是否有权限
     *
     * @var boolean
     */
    protected $boolCheckAuth = false;

    /**
     * 是否校内网IP
     *
     * @var boolean
     */
    protected $boolCheckIp = true;

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'int|required',
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
<?php
/**
 * @name Action_CreateReserveOrderWrite
 * @desc Action_CreateReserveOrderWrite
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_CreateReserveOrderWrite extends Order_Base_Action
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
        'purchase_order_id' => 'int|required',
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
        $this->objPage = new Service_Page_Reserve_CreateReserveOrderWrite();
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
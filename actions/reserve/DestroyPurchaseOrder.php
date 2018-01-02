<?php
/**
 * @name Action_DestroyPurchaseOrder
 * @desc Action_DestroyPurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_DestroyPurchaseOrder extends Order_Base_Action
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
        $this->objPage = new Service_Page_Purchase_DestroyPurchaseOrder();
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
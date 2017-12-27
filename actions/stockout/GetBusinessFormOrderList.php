<?php
/**
 * @name Action_DeliveryOrder
 * @desc 查询业态订单列表
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetBusinessFormOrderList extends Order_Base_Action
{
    protected $boolCheckLogin = false;
    protected $boolCheckAuth = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required',
        'warehouse_id' => 'int',
        'business_form_order_id' => 'int',
        'business_form_order_status' => 'int',
        'business_form_order_type' => 'int',
        'customer_name' => 'str',
        'customer_id' => 'int',
        'start_time' => 'int',
        'end_time' => 'int',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_GetBusinessFormOrderList
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_GetBusinessFormOrderList();
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
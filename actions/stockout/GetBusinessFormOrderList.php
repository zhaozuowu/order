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
        'page_num' => 'int',
        'page_size' => 'int|required',
        'status' => 'int|required',
        'warehouse_id' => 'int',
        'business_form_order_id' => 'int',
        'business_form_order_status' => 'int',
        'business_form_order_type' => 'int',
        'customer_name' => 'string',
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
     * @var Service_Page_DeliveryOrder
     */
    private $objDeliveryOrder;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objDeliveryOrder = new Service_Page_GetBusinessFormOrderList();
    }

    /**
     * execute
     * @return array
     */
    public function myExecute()
    {
        return $this->objDeliveryOrder->execute($this->arrFilterResult);
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
<?php
/**
 * @name Action_GetBusinessFormOrderList
 * @desc 查询业态订单列表
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetBusinessFormOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required|max[200]',
        'status'    => 'int|required',
        'warehouse_ids' => 'str|required',
        'business_form_order_id' => 'int',
        'business_form_order_type' => 'int',
        'customer_name' => 'str',
        'customer_id' => 'str',
        'start_time' => 'int|required',
        'end_time' => 'int|required',
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
        $this->objPage = new Service_Page_Business_GetBusinessFormOrderList();
    }

    /**
     * format result
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {

        if (empty($arrRet['orders'])) {
            return $arrRet;
        }
        $arrRetList = $arrRet['orders'];
        $arrFormatRet = [];
        foreach ($arrRetList as $arrItem) {
            $arrFormatItem = [];
            $arrFormatItem['business_form_order_id'] = empty($arrItem['business_form_order_id']) ? 0 : intval($arrItem['business_form_order_id']);
            $arrFormatItem['business_form_order_status'] = empty($arrItem['status']) ? '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_STATUS_LIST[$arrItem['status']];
            $arrFormatItem['business_form_order_type'] = empty($arrItem['business_form_order_type']) ? '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrItem['business_form_order_type']];
            $arrFormatItem['warehouse_name'] = empty($arrItem['warehouse_name']) ? '' : $arrItem['warehouse_name'];
            $arrFormatItem['customer_id'] = empty($arrItem['customer_id']) ? '' : $arrItem['customer_id'];
            $arrFormatItem['customer_name'] = empty($arrItem['customer_name']) ? '' : ($arrItem['customer_name']);
            $arrFormatItem['order_amount'] = empty($arrItem['order_amount']) ? 0 : intval($arrItem['order_amount']);
            $arrFormatItem['customer_city_id'] = empty($arrItem['customer_city_id']) ? 0 : intval($arrItem['customer_city_id']);
            $arrFormatItem['customer_city_name'] = empty($arrItem['customer_city_name']) ? '' : $arrItem['customer_city_name'];
            $arrFormatItem['distribute_amount'] = empty($arrItem['distribute_amount']) ? 0 : intval($arrItem['distribute_amount']);
            $arrFormatItem['create_time'] = empty($arrItem['create_time']) ? 0 : date('Y-m-d H:i:s', $arrItem['create_time']);
            $arrFormatRet[] = $arrFormatItem;


        }
        return [
            'total' => $arrRet['total'],
            'orders' => $arrFormatRet,
        ];
    }


}
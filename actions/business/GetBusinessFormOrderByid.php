<?php
/**
 * @name Action_GetBusinessFormOrderByid
 * @desc 查询业态订单明细
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetBusinessFormOrderByid extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'business_form_order_id' => 'int|required',
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

        $this->objPage = new Service_Page_Business_GetBusinessFormOrderByid();
    }

    /**
     * format result
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $ret = [];
        if (empty($arrRet)) {
            return $ret;
        }
        $arrFormatRet = [
            'business_form_order_id' => empty($arrRet['business_form_order_id']) ? 0 : intval($arrRet['business_form_order_id']),
            'business_form_status' => empty($arrRet['status']) ? 0 : intval($arrRet['status']),
            'business_form_order_status_text' => empty($arrRet['status']) ? '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_STATUS_LIST[$arrRet['status']],
            'business_form_order_type' => empty($arrRet['business_form_order_type']) ? 0 : intval($arrRet['business_form_order_type']),
            'business_form_order_type_text' => empty($arrRet['business_form_order_type']) ? '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrRet['business_form_order_type']],
            'order_supply_type' => empty($arrRet['order_supply_type']) ? 0 : intval($arrRet['order_supply_type']),
            'order_supply_type_text' => empty($arrRet['order_supply_type']) ? '' : Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrRet['order_supply_type']],
            'create_time' => empty($arrRet['create_time']) ? 0 : date('Y-m-d H:i:s', $arrRet['create_time']),
            'order_amount' => empty($arrRet['order_amount']) ? 0 : intval($arrRet['order_amount']),
            'total_price' => empty($arrRet['business_form_order_price']) ? 0 : intval($arrRet['business_form_order_price']),
            'business_form_order_remark' => empty($arrRet['business_form_order_remark']) ? '' : $arrRet['business_form_order_remark'],
            'warehouse_name' => empty($arrRet['warehouse_name']) ? '' : $arrRet['warehouse_name'],
            'customer_id' => empty($arrRet['customer_id']) ? '' : $arrRet['customer_id'],
            'customer_name' => empty($arrRet['customer_name']) ? '' : $arrRet['customer_name'],
            'customer_address' => empty($arrRet['customer_address']) ? '' : $arrRet['customer_address'],
            'customer_contactor' => empty($arrRet['customer_contactor']) ? '' : $arrRet['customer_contactor'],
            'customer_contact' => empty($arrRet['customer_contact']) ? '' : $arrRet['customer_contact'],
            'skus' => empty($arrRet['skus']) ? [] : $arrRet['skus'],
        ];
        return $arrFormatRet;
    }


}
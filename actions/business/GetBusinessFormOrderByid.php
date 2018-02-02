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
            'create_time' => empty($arrRet['create_time']) ? 0 : date('Y-m-d H:i:s', $arrRet['create_time']),
            'order_amount' => empty($arrRet['order_amount']) ? 0 : intval($arrRet['order_amount']),
            'business_form_order_remark' => empty($arrRet['business_form_order_remark']) ? '' : $arrRet['business_form_order_remark'],
            'warehouse_name' => empty($arrRet['warehouse_name']) ? '' : $arrRet['warehouse_name'],
            'customer_id' => empty($arrRet['customer_id']) ? '' : $arrRet['customer_id'],
            'customer_name' => empty($arrRet['customer_name']) ? '' : $arrRet['customer_name'],
            'customer_address' => empty($arrRet['customer_address']) ? '' : $arrRet['customer_address'],
            'customer_contactor' => empty($arrRet['customer_contactor']) ? '' : $arrRet['customer_contactor'],
            'customer_contact' => empty($arrRet['customer_contact']) ? '' : $arrRet['customer_contact'],
             'executor' => empty($arrRet['executor'])? '' : $arrRet['executor'],
             'executor_contact' => empty($arrRet['executor_contact'])? '' : $arrRet['executor_contact'],
            'skus' => empty($arrRet['skus']) ? [] : $this->formatSku($arrRet['skus']),
        ];
        //格式化设备信息
        $arrShelfInfo = json_decode($arrRet['shelf_info'], true);
        $arrFormatRet['order_supply_type_text'] = empty($arrShelfInfo['supply_type']) ?
            '' : Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrShelfInfo['supply_type']];
        $arrFormatRet['devices'] = Order_Define_Format::formatDevices($arrShelfInfo['devices']);
        return $arrFormatRet;
    }

    private function formatSku($skus)
    {
        foreach ($skus as $key => $item) {
           unset($skus[$key]['stock_amount']);
           unset($skus[$key]['cost_price']);
           unset($skus[$key]['cost_total_price']);
           unset($skus[$key]['send_price']);
           unset($skus[$key]['send_total_price']);
           $skus[$key]['sku_net_text'] = $item['sku_net'].Order_Define_Sku::SKU_NET_MAP[$item['sku_net_unit']];
           $skus[$key]['upc_unit_num'] = empty($item['upc_unit_num']) ? '' : '1*' . $item['upc_unit_num'];
           $skus[$key]['upc_unit'] = isset(Order_Define_StockoutOrder::UPC_UNIT[$item['upc_unit']]) ? Order_Define_StockoutOrder::UPC_UNIT[$item['upc_unit']]:'';
        }
        return $skus;
    }


}
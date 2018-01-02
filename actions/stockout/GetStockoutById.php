<?php
/**
 * @name Action_GetStockoutById
 * @desc 查询出库单明细
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetStockoutById extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'int|required',
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

        $this->objPage = new Service_Page_Stockout_GetStockoutById();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {

        $ret = [];
        if (empty($data)) {
            return $ret;
        }
        $arrRet = $data['stockout_order_info'];
        $arrFormatRet['stockoutinfo'] = [
            'stockout_order_id' => empty($arrRet['stockout_order_id']) ? 0 : intval($arrRet['stockout_order_id']),
            'stockout_order_status' => empty($arrRet['stockout_order_status']) ? '' : Order_Define_StockoutOrder::STOCK_OUT_ORDER_STATUS_LIST[$arrRet['stockout_order_status']],
            'business_form_order_id' => empty($arrRet['business_form_order_id']) ? 0 : intval($arrRet['business_form_order_id']),
            'warehouse_name' => empty($arrRet['warehouse_name']) ? '' : intval($arrRet['warehouse_name']),
            'stockout_order_type' => empty($arrRet['stockout_order_type']) ? '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_List[$arrRet['stockout_order_type']],
            'stockout_order_source' => empty($arrRet['stockout_order_source']) ? '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_SOURCE_LIST[$arrRet['stockout_order_source']],
            'stockout_create_time' => empty($arrRet['stockout_create_time']) ? 0 : date('Y-m-d H:i:s', $arrRet['stockout_create_time']),
            'stockout_expect_send_time' => empty($arrRet['expect_send_time']) ? 0 : date('Y-m-d H:i:s', $arrRet['expect_send_time']),
            'stockout_order_total_price' => empty($arrRet['stockout_order_total_price']) ? 0 : $arrRet['stockout_order_total_price'],
            'stockout_order_amount' => empty($arrRet['stockout_order_amount']) ? 0 : $arrRet['stockout_order_amount'],
            'stockout_order_distribute_amount' => empty($arrRet['stockout_order_distribute_amount']) ? 0 : $arrRet['stockout_order_distribute_amount'],
            'stockout_order_pickup_amount' => empty($arrRet['stockout_order_pickup_amount']) ? 0 : $arrRet['stockout_order_pickup_amount'],
            'stockout_order_remark' => empty($arrRet['stockout_order_remark']) ? '' : $arrRet['stockout_order_remark'],

        ];
        $arrFormatRet['customerinfo'] = [
            'customer_id' => empty($arrRet['customer_id']) ? 0 : intval($arrRet['customer_id']),
            'customer_name' => empty($arrRet['customer_name']) ? 0 : intval($arrRet['customer_name']),
            'customer_contactor' => empty($arrRet['customer_contactor']) ? '' : intval($arrRet['customer_contactor']),
            'customer_contact' => empty($arrRet['customer_contact']) ? '' : intval($arrRet['customer_contact']),
            'customer_address' => empty($arrRet['customer_address']) ? '' : intval($arrRet['customer_address']),
        ];
        foreach ($data['stockout_order_sku'] as $arrItem) {
            $arrFormatItem = [];
            $arrFormatItem['sku_id'] = empty($arrItem['sku_id']) ? 0 : intval($arrItem['sku_id']);
            $arrFormatItem['upc_id'] = empty($arrItem['upc_id']) ? '' : $arrItem['upc_id'];
            $arrFormatItem['sku_name'] = empty($arrItem['sku_name']) ? '' : [$arrItem['sku_name']];
            $arrFormatItem['sku_net'] = empty($arrItem['sku_net']) ? '' : [$arrItem['sku_net']];
            $arrFormatItem['upc_unit_num'] = empty($arrItem['upc_unit_num']) ? '' : [$arrItem['upc_unit_num']];
            $arrFormatItem['upc_unit'] = empty($arrItem['upc_unit']) ? '' : Order_Define_StockoutOrder::UPC_UNIT[$arrItem['upc_unit']];
            $arrFormatItem['cost_price'] = empty($arrItem['cost_price']) ? 0 : $arrItem['cost_price'];
            $arrFormatItem['cost_total_price'] = empty($arrItem['cost_total_price']) ? 0 : $arrItem['cost_total_price'];
            $arrFormatItem['send_price'] = empty($arrItem['send_price']) ? 0 : $arrItem['send_price'];
            $arrFormatItem['send_total_price'] = empty($arrItem['send_total_price']) ? 0 : $arrItem['send_total_price'];
            $arrFormatItem['order_amount'] = empty($arrItem['order_amount']) ? 0 : $arrItem['order_amount'];
            $arrFormatItem['distribute_amount'] = empty($arrItem['distribute_amount']) ? 0 : $arrItem['distribute_amount'];
            $arrFormatItem['pickup_amount'] = empty($arrItem['pickup_amount']) ? 0 : $arrItem['pickup_amount'];
            $arrFormatRet['stockout_order_skuinfo'][] = $arrFormatItem;
        }
        return $arrFormatRet;


    }

}
<?php
/**
 * @name Action_GetStockoutOrderList
 * @desc 查询出库单列表
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_GetStockoutOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required|max[200]',
        'status' => 'int|default[0]',
        'warehouse_ids' => 'str|required',
        'stockout_order_id' => 'str',
        'business_form_order_id' => 'int',
        'customer_name' => 'str',
        'customer_id' => 'str',
        'shipment_order_id'=>'int',
        'is_print' => 'int',
        'stockout_order_status' => 'int',
        'logistics_order_id'=>'str',
        'stockout_order_source'=>'int',
        'start_time' => 'int|required',
        'end_time' => 'int|required',
        'data_source' => 'int',
        'is_pickup_ordered' => 'int|default[0]',
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
        $this->objPage = new Service_Page_Stockout_GetStockoutOrderList();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatRet = [];
        $arrFormatRet['total'] = $arrRet['total'];
        $arrFormatRet['orders'] = [];
        foreach ((array)$arrRet['orders'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : Nscm_Define_OrderPrefix::SOO.$arrRetItem['stockout_order_id'];
            $arrFormatRetItem['stockout_order_source'] = empty($arrRetItem['stockout_order_source']) ?  '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrRetItem['stockout_order_source']];
            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type']) ? 0 : $arrRetItem['stockout_order_type'];
            $arrFormatRetItem['stockout_order_type_text'] = empty($arrRetItem['stockout_order_type']) ?
                '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrRetItem['stockout_order_type']];
            $arrFormatRetItem['business_form_order_id'] = empty($arrRetItem['business_form_order_id']) ? 0 : $arrRetItem['business_form_order_id'];
            $arrFormatRetItem['stockout_order_status'] = empty($arrRetItem['stockout_order_status']) ? 0 : $arrRetItem['stockout_order_status'];
            $arrFormatRetItem['stockout_order_status_text'] = empty($arrRetItem['stockout_order_status']) ?
                '' : Order_Define_StockoutOrder::STOCK_OUT_ORDER_STATUS_LIST[$arrRetItem['stockout_order_status']];
            $arrFormatRetItem['is_print'] = empty($arrRetItem['stockout_order_is_print']) ? 0 : $arrRetItem['stockout_order_is_print'];
            $arrFormatRetItem['is_print_text'] = empty($arrRetItem['stockout_order_is_print']) ?
                '' : Order_Define_StockoutOrder::STOCKOUT_PRINT_STATUS[$arrRetItem['stockout_order_is_print']];
            $arrFormatRetItem['is_pickup_ordered'] = empty($arrRetItem['is_pickup_ordered']) ? 0 : $arrRetItem['is_pickup_ordered'];
            $arrFormatRetItem['is_pickup_ordered_text'] = empty($arrRetItem['is_pickup_ordered']) ?
                '' : Order_Define_StockoutOrder::PICKUP_ORDER_TYPE_MAP[$arrRetItem['is_pickup_ordered']];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['signup_status'] = empty($arrRetItem['signup_status']) ? '' : Order_Define_StockoutOrder::STOCKOUT_SIGINUP_STATUS_LIST[$arrRetItem['signup_status']];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? '' : $arrRetItem['customer_id'];
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '' : $arrRetItem['customer_name'];
            $arrFormatRetItem['stockout_order_amount'] = empty($arrRetItem['stockout_order_amount']) ? 0 : $arrRetItem['stockout_order_amount'];
            $arrFormatRetItem['distribute_amount'] = empty($arrRetItem['stockout_order_distribute_amount']) ? 0 : $arrRetItem['stockout_order_distribute_amount'];
            $arrFormatRetItem['pickup_amount'] = empty($arrRetItem['stockout_order_pickup_amount']) ? 0 : $arrRetItem['stockout_order_pickup_amount'];
            $arrFormatRetItem['create_time'] = empty($arrRetItem['create_time']) ? '' : date("Y-m-d H:i:s", $arrRetItem['create_time']);
            $arrFormatRetItem['customer_city_id'] = empty($arrRetItem['customer_city_id']) ? 0 : intval($arrRetItem['customer_city_id']);
            $arrFormatRetItem['customer_city_name'] = empty($arrRetItem['customer_city_name']) ? '' : $arrRetItem['customer_city_name'];
            $arrFormatRetItem['data_source'] = isset(Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_TYPE_MAP[intval($arrRetItem['data_source'])]) ? Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_TYPE_MAP[intval($arrRetItem['data_source'])]:
                Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_TYPE_MAP[Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_SYSTEM_ORDER];
            // 人工录入类型，不显示无关联订单与订单状态
            if( Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_MANUAL_INPUT == intval($arrRetItem['data_source'])){
                $arrFormatRetItem['business_form_order_id'] = Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            }
            $arrFormatRetItem['logistics_order_id'] = empty($arrRetItem['logistics_order_id']) ? '' : $arrRetItem['logistics_order_id'];
            $arrFormatRetItem['shipment_order_id'] = empty($arrRetItem['shipment_order_id']) ? 0 : $arrRetItem['shipment_order_id'];

            $arrFormatRet['orders'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

}
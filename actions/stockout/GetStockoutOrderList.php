<?php
/**
 * @name Action_GetStockoutOrderList
 * @desc 查询出库单列表
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_GetStockoutOrderList extends Order_Base_Action
{
    protected $boolCheckAuth = false;
    protected $boolCheckLogin = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required|max[100]',
        'status' => 'int|default[0]',
        'warehouse_id' => 'int',
        'stockout_order_id' => 'str',
        'business_form_order_id' => 'int',
        'customer_name' => 'str',
        'customer_id' => 'int',
        'is_print' => 'int',
        'stockout_order_status' => 'int',
        'start_time' => 'int',
        'end_time' => 'int',
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
        foreach((array)$arrRet['orders'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : 'SSO'.$arrRetItem['stockout_order_id'];
            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type']) ? 0 : $arrRetItem['stockout_order_type'];
            $arrFormatRetItem['stockout_order_type_text'] = empty($arrRetItem['stockout_order_type']) ? 
                                                                '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrRetItem['stockout_order_type']];
            $arrFormatRetItem['business_form_order_id'] = empty($arrRetItem['business_form_order_id']) ? 0 : $arrRetItem['business_form_order_id'];
            $arrFormatRetItem['stockout_order_status'] = empty($arrRetItem['stockout_order_status']) ? 0 : $arrRetItem['stockout_order_status'];
            $arrFormatRetItem['stockout_order_status_text'] = empty($arrRetItem['stockout_order_status']) ? 
                                                                '' : Order_Define_StockoutOrder::STOCK_OUT_ORDER_STATUS_LIST[$arrRetItem['stockout_order_status']];
            $arrFormatRetItem['is_print'] = empty($arrRetItem['is_print']) ? 0 : $arrRetItem['is_print'];
            $arrFormatRetItem['is_print_text'] = empty($arrRetItem['is_print']) ? 
                                                    '' : Order_Define_StockoutOrder::STOCKOUT_PRINT_STATUS[$arrRetItem['is_print']];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? '' : $arrRetItem['customer_id'];
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '' : $arrRetItem['customer_name'];
            $arrFormatRetItem['stockout_order_amount'] = empty($arrRetItem['stockout_order_amount']) ? '' : $arrRetItem['stockout_order_amount'];
            $arrFormatRetItem['distribute_amount'] = empty($arrRetItem['distribute_amount']) ? 0 : $arrRetItem['distribute_amount'];
            $arrFormatRetItem['pickup_amount'] = empty($arrRetItem['pickup_amount']) ? 0 : $arrRetItem['pickup_amount'];
            $arrFormatRetItem['create_time'] = empty($arrRetItem['create_time']) ? '' : date("Y-m-d H:i:s", $arrRetItem['create_time']);
            $arrFormatRet['orders'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

}
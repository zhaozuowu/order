<?php
/**
 * @name Action_GetStockoutDetail
 * @desc 销售出库明细
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetStockoutDetail extends Order_Base_ApiAction
{
    /**
     * 是否校内网IP
     *
     * @var boolean
     */
    protected $boolCheckIp = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'start_time'=>'int|required',
        'end_time'=>'int|required',
        'page_size' => 'int|required',
        'page_num' => 'int|default[1]',
        'warehouse_ids'=>'str',
        'stockout_order_ids'=>'str',
        'business_form_order_id'=>'int',
        'sku_name'=>'str',
        'sku_id'=>'int',
        'customer_name'=>'str',
        'customer_id'=>'int',
        'customer_name'=>'str',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_GetStockoutDetail();
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
        foreach((array)$arrRet['list'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : 'SSO'.$arrRetItem['stockout_order_id'];
            $arrFormatRetItem['create_time'] = empty($arrRetItem['create_time']) ?  0 : date('Y-m-d H:i:s',$arrRetItem['create_time']);
            $arrFormatRetItem['business_form_order_id'] = empty($arrRetItem['business_form_order_id']) ?  0 : $arrRetItem['business_form_order_id'];
            $arrFormatRetItem['stockout_order_status'] = empty($arrRetItem['stockout_order_status']) ?  '' : Order_Define_StockoutOrderDetail::STOCKOUT_ORDER_STATUS_TEXT_MAP[$arrRetItem['stockout_order_status']];
            $arrFormatRetItem['city_id'] = empty($arrRetItem['city_id']) ?  0 : $arrRetItem['city_id'];
            $arrFormatRetItem['city_name'] = empty($arrRetItem['city_name']) ?  '' : $arrRetItem['city_name'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ?  '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['warehouse_id'] = empty($arrRetItem['warehouse_id']) ?  0 : $arrRetItem['warehouse_id'];
            $arrFormatRetItem['business_form_order_type'] = empty($arrRetItem['stockout_order_source_describle']) ?  '' : $arrRetItem['stockout_order_source_describle'];
            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type_describle']) ?  '' : $arrRetItem['stockout_order_type_describle'];
            $arrFormatRetItem['business_form_create_time'] = empty($arrRetItem['order_create_time']) ?  0 : date('Y-m-d H:i:s',$arrRetItem['order_create_time']);
            $arrFormatRetItem['expect_send_time'] = date('Y-m-d H:i:s',$arrRetItem['expect_arrive_start_time']).'~'.date('Y-m-d H:i:s',$arrRetItem['expect_arrive_start_time']);
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '':$arrRetItem['customer_name'];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? 0 :$arrRetItem['customer_id'];
            $arrFormatRetItem['customer_contactor'] = empty($arrRetItem['customer_contactor']) ? '' :$arrRetItem['customer_contactor'];
            $arrFormatRetItem['sku_id'] = empty($arrRetItem['sku_id']) ? 0 :$arrRetItem['sku_id'];
            $arrFormatRetItem['upc_id'] = empty($arrRetItem['upc_id']) ? '' :$arrRetItem['upc_id'];
            $arrFormatRetItem['sku_name'] = empty($arrRetItem['sku_name']) ? '' :$arrRetItem['sku_name'];
            $arrFormatRetItem['sku_category_1'] = empty($arrRetItem['sku_category_1']) ? 0 :$arrRetItem['sku_category_1'];
            $arrFormatRetItem['sku_category_2'] = empty($arrRetItem['sku_category_2']) ? 0 :$arrRetItem['sku_category_2'];
            $arrFormatRetItem['sku_category_3'] = empty($arrRetItem['sku_category_3']) ? 0 :$arrRetItem['sku_category_3'];
            $arrFormatRetItem['sku_category_text'] =(empty($arrRetItem['sku_category_1']) && $arrRetItem['sku_category_2'] && $arrRetItem['sku_category_3'] ) ? '':$arrRetItem['category_1_text'].'/'.$arrRetItem['category_2_text'].'/'.$arrRetItem['category_3_text'];
            $arrFormatRetItem['sku_net'] =  empty($arrRetItem['sku_net']) ? '' :$arrRetItem['sku_net'];
            $arrFormatRetItem['is_import'] =  empty($arrRetItem['import_describle']) ? '' :$arrRetItem['import_describle'];
            $arrFormatRetItem['upc_unit'] =  empty($arrRetItem['upc_unit']) ? 0 :$arrRetItem['upc_unit'];
            $arrFormatRetItem['upc_unit_text'] =  empty($arrRetItem['upc_unit_text']) ? '' :$arrRetItem['upc_unit_text'];
            $arrFormatRetItem['order_amount'] =  empty($arrRetItem['order_amount']) ? 0 :$arrRetItem['order_amount'];
            $arrFormatRetItem['distribute_amount'] =  empty($arrRetItem['distribute_amount']) ? 0 :$arrRetItem['distribute_amount'];
            $arrFormatRetItem['pickup_amount'] =  empty($arrRetItem['pickup_amount']) ? 0 :$arrRetItem['pickup_amount'];
            $arrFormatRetItem['sku_effect_type'] =  empty($arrRetItem['sku_effect_type']) ? '' :Order_Define_StockoutOrderDetail::SKU_EFFECT_TYPE[$arrRetItem['sku_effect_type']];
            $arrFormatRetItem['sku_effect_day'] =  empty($arrRetItem['sku_effect_day']) ? 0:date('Y-m-d H:i:s',$arrRetItem['sku_effect_day']);
            $arrFormatRetItem['cost_untaxed_price'] =  empty($arrRetItem['cost_price']) ? 0 :$arrRetItem['cost_price'];
            $arrFormatRetItem['cost_taxed_price'] =  empty($arrRetItem['cost_price_tax']) ? 0 :$arrRetItem['cost_price_tax'];
            $arrFormatRetItem['total_cost_untaxed_price'] =  empty($arrRetItem['cost_total_price']) ? 0 :$arrRetItem['cost_total_price'];
            $arrFormatRetItem['total_cost_taxed_price'] =  empty($arrRetItem['cost_total_price_tax']) ? 0 :$arrRetItem['cost_total_price_tax'];
            $arrFormatRetItem['send_untaxed_price'] =  empty($arrRetItem['send_price']) ? 0 :$arrRetItem['send_price'];
            $arrFormatRetItem['send_taxed_price'] =  empty($arrRetItem['send_price_tax']) ? 0 :$arrRetItem['send_price_tax'];
            $arrFormatRetItem['total_send_untaxed_price'] =  empty($arrRetItem['send_total_price']) ? 0 :$arrRetItem['send_total_price'];
            $arrFormatRetItem['total_send_taxed_price'] =  empty($arrRetItem['send_total_price_tax']) ? 0 :$arrRetItem['send_total_price_tax'];
            $arrFormatRetItem['delivery_order_id'] =  empty($arrRetItem['logistics_order_id']) ? 0 :$arrRetItem['logistics_order_id'];
            $arrFormatRet['list'][] = $arrFormatRetItem;

        }
        return $arrFormatRet;
    }

}
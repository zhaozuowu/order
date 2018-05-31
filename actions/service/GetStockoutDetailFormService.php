<?php
/**
 * @name Action_GetStockoutDetailFormApi
 * @desc 销售出库明细
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_Service_GetStockoutDetailFormService extends Order_Base_ServiceAction
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
        'order_create_start_time'=>'int|required',
        'order_create_end_time'=>'int|required',
        'page_size' => 'int|required',
        'page_num' => 'int|default[1]',
        'warehouse_ids'=>'str',
        'stockout_order_ids'=>'str',
        'stockout_order_id'=>'str',
        'business_form_order_id'=>'int',
        'sku_name'=>'str',
        'sku_id'=>'int',
        'customer_name'=>'str',
        'customer_id'=>'str',
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
        $arrFormatRet['list'] = [];
        foreach((array)$arrRet['list'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : Nscm_Define_OrderPrefix::SOO.$arrRetItem['stockout_order_id'];
            $arrFormatRetItem['create_time'] = empty($arrRetItem['create_time']) ?  0 : date('Y-m-d H:i:s',$arrRetItem['create_time']);
            $arrFormatRetItem['business_form_order_id'] = empty($arrRetItem['business_form_order_id']) ?  0 : $arrRetItem['business_form_order_id'];
            $arrFormatRetItem['stockout_order_status'] = empty($arrRetItem['stockout_order_status_describle']) ?  '' :$arrRetItem['stockout_order_status_describle'];
            $arrFormatRetItem['city_id'] = empty($arrRetItem['city_id']) ?  0 : $arrRetItem['city_id'];
            $arrFormatRetItem['city_name'] = empty($arrRetItem['city_name']) ?  '' : $arrRetItem['city_name'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ?  '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['warehouse_id'] = empty($arrRetItem['warehouse_id']) ?  0 : $arrRetItem['warehouse_id'];
            $arrFormatRetItem['business_form_order_type'] = empty($arrRetItem['stockout_order_source_describle']) ?  '' : $arrRetItem['stockout_order_source_describle'];
            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type_describle']) ?  '' : $arrRetItem['stockout_order_type_describle'];
            $arrFormatRetItem['business_form_create_time'] = empty($arrRetItem['order_create_time']) ?  0 : date('Y-m-d H:i:s',$arrRetItem['order_create_time']);
            $arrFormatRetItem['expect_send_time'] = date('Y-m-d H:i:s',$arrRetItem['expect_arrive_start_time']).'~'.date('Y-m-d H:i:s',$arrRetItem['expect_arrive_end_time']);
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '':$arrRetItem['customer_name'];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? '' :$arrRetItem['customer_id'];
            $arrFormatRetItem['customer_contactor'] = empty($arrRetItem['customer_contactor']) ? '' :$arrRetItem['customer_contactor'];
            $arrFormatRetItem['customer_contact'] = empty($arrRetItem['customer_contact']) ? '' :$arrRetItem['customer_contact']; 
            $arrFormatRetItem['sku_id'] = empty($arrRetItem['sku_id']) ? 0 :$arrRetItem['sku_id'];
            $arrFormatRetItem['upc_id'] = empty($arrRetItem['upc_id']) ? '' :$arrRetItem['upc_id'];
            $arrFormatRetItem['sku_name'] = empty($arrRetItem['sku_name']) ? '' :$arrRetItem['sku_name'];
            $arrFormatRetItem['sku_category_1'] = empty($arrRetItem['category_1_text']) ? '' :$arrRetItem['category_1_text'];
            $arrFormatRetItem['sku_category_2'] = empty($arrRetItem['category_2_text']) ? '' :$arrRetItem['category_2_text'];
            $arrFormatRetItem['sku_category_3'] = empty($arrRetItem['category_3_text']) ? '' :$arrRetItem['category_3_text'];
            $arrFormatRetItem['sku_category_text'] =(empty($arrRetItem['category_1']) && $arrRetItem['category_2'] && $arrRetItem['category_3'] ) ? '':$arrRetItem['category_1_text'].'/'.$arrRetItem['category_2_text'].'/'.$arrRetItem['category_3_text'];
            $skuNeText = isset(Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]) ? Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]:'';
            $arrFormatRetItem['sku_net'] = $arrRetItem['sku_net'].$skuNeText;
            $arrFormatRetItem['is_import'] = isset(Order_Define_Sku::SKU_FROM_COUNTRY_MAP[$arrRetItem['import']]) ? Order_Define_Sku::SKU_FROM_COUNTRY_MAP[$arrRetItem['import']]:'';
            $arrFormatRetItem['upc_unit'] =  empty($arrRetItem['upc_unit']) ? 0 :$arrRetItem['upc_unit'];
            $arrFormatRetItem['upc_unit_text'] =  empty($arrRetItem['upc_unit_text']) ? '' :$arrRetItem['upc_unit_text'];
            $arrFormatRetItem['order_amount'] =  empty($arrRetItem['order_amount']) ? 0 :$arrRetItem['order_amount'];
            $arrFormatRetItem['distribute_amount'] =  empty($arrRetItem['distribute_amount']) ? 0 :$arrRetItem['distribute_amount'];
            $arrFormatRetItem['pickup_amount'] =  empty($arrRetItem['pickup_amount']) ? 0 :$arrRetItem['pickup_amount'];
            $arrFormatRetItem['sku_effect_type'] =  empty($arrRetItem['sku_effect_type']) ? '' :Order_Define_StockoutOrderDetail::SKU_EFFECT_TYPE[$arrRetItem['sku_effect_type']];
            $arrFormatRetItem['sku_effect_day'] =  empty($arrRetItem['sku_effect_day']) ? 0:$arrRetItem['sku_effect_day'];
            $arrFormatRetItem['cost_untaxed_price'] =  empty($arrRetItem['cost_price']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_price']));
            $arrFormatRetItem['cost_taxed_price'] =  empty($arrRetItem['cost_price_tax']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_price_tax']));
            $arrFormatRetItem['total_cost_untaxed_price'] =  empty($arrRetItem['cost_total_price']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_total_price']));
            $arrFormatRetItem['total_cost_taxed_price'] =  empty($arrRetItem['cost_total_price_tax']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_total_price_tax']));
            $arrFormatRetItem['send_untaxed_price'] =  empty($arrRetItem['send_price']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['send_price']));
            $arrFormatRetItem['send_taxed_price'] =  empty($arrRetItem['send_price_tax']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['send_price_tax']));
            $arrFormatRetItem['total_send_untaxed_price'] =  empty($arrRetItem['send_total_price']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['send_total_price']));
            $arrFormatRetItem['total_send_taxed_price'] =  empty($arrRetItem['send_total_price_tax']) ? 0 :sprintf('%0.2f',Nscm_Service_Price::convertDefaultToYuan($arrRetItem['send_total_price_tax']));
            $arrFormatRetItem['delivery_order_id'] =  empty($arrRetItem['logistics_order_id']) ? 0 :$arrRetItem['logistics_order_id'];
            $arrFormatRetItem['stockout_order_remark'] =  empty($arrRetItem['stockout_order_remark']) ? '' :$arrRetItem['stockout_order_remark'];
            $arrFormatRet['list'][] = $arrFormatRetItem;

        }
        Nscm_Service_Format_Data::filterIllegalData($arrFormatRet['list']);
        return $arrFormatRet;
    }

}
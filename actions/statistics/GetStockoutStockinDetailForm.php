<?php
/**
 * @name Action_GetStockoutStockinDetailForm
 * @desc 报表-获取销退入库明细（分页），注释：接口只查询入库类型为 销退入库 的数据
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockoutStockinDetailForm extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids' => 'str',
        'stockin_order_id' => 'regex|patern[/^(SIO\d{13})?$/]',
        'source_order_id' => 'regex|patern[/^(SOO\d{13})?$/]',
        'sku_id' => 'int',
        'client_id' => 'int',
        'client_name' => 'str',
        'stockin_time_start' => 'int',
        'stockin_time_end' => 'int',
        'page_num' => 'int|default[1]|min[1]',
        'page_size' => 'int|required|min[1]|max[200]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * construct function
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Statistics_GetStockoutStockinDetailForm();
    }

    /**
     * format result, output data format process
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [
            'list' => [],
            'total' => 0,
        ];

        // 返回结果数据
        if (empty($arrRet['list'])) {
            return $arrFormatResult;
        }

        $arrRetList = $arrRet['list'];

        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['stockin_order_id'] = empty($arrListItem['stockin_order_id']) ? '' : Nscm_Define_OrderPrefix::SIO . intval($arrListItem['stockin_order_id']);
            $arrRoundResult['source_order_id'] = empty($arrListItem['source_order_id']) ? '' : Nscm_Define_OrderPrefix::SOO . intval($arrListItem['source_order_id']);
            $arrRoundResult['city_name'] = empty($arrListItem['city_name']) ? '' : strval($arrListItem['city_name']);
            $arrRoundResult['city_id'] = empty($arrListItem['city_id']) ? '' : intval($arrListItem['city_id']);
            $arrRoundResult['warehouse_id'] = empty($arrListItem['warehouse_id']) ? '' : intval($arrListItem['warehouse_id']);
            $arrRoundResult['warehouse_name'] = empty($arrListItem['warehouse_name']) ? '' : intval($arrListItem['warehouse_name']);
            $arrRoundResult['stockin_order_type'] = empty($arrListItem['stockin_order_type']) ? '' : intval($arrListItem['stockin_order_type']);
            // 手动返回 销退入库 文本介绍
            $arrRoundResult['stockin_order_type_text'] = '销退入库';
            $arrRoundResult['stockin_time'] = empty($arrListItem['stockin_time']) ? '' : intval($arrListItem['stockin_time']);
            $arrRoundResult['stockin_time_text'] = empty($arrListItem['stockin_time_text']) ? '' : strval($arrListItem['stockin_time_text']);
            $arrRoundResult['stockin_batch_id'] = empty($arrListItem['stockin_batch_id']) ? '' : intval($arrListItem['stockin_batch_id']);
            $arrRoundResult['client_name'] = empty($arrListItem['client_name']) ? '' : strval($arrListItem['client_name']);
            $arrRoundResult['client_id'] = empty($arrListItem['client_id']) ? '' : intval($arrListItem['client_id']);
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? '' : intval($arrListItem['sku_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? '' : strval($arrListItem['sku_name']);
            $arrRoundResult['sku_category_1'] = empty($arrListItem['sku_category_1']) ? '' : intval($arrListItem['sku_category_1']);
            $arrRoundResult['sku_category_2'] = empty($arrListItem['sku_category_2']) ? '' : intval($arrListItem['sku_category_2']);
            $arrRoundResult['sku_category_3'] = empty($arrListItem['sku_category_3']) ? '' : intval($arrListItem['sku_category_3']);
            $arrRoundResult['sku_category_1_text'] = empty($arrListItem['sku_category_1_text']) ? '' : strval($arrListItem['sku_category_1_text']);
            $arrRoundResult['sku_category_2_text'] = empty($arrListItem['sku_category_2_text']) ? '' : strval($arrListItem['sku_category_2_text']);
            $arrRoundResult['sku_category_3_text'] = empty($arrListItem['sku_category_3_text']) ? '' : strval($arrListItem['sku_category_3_text']);
            $arrRoundResult['sku_from_country'] = empty($arrListItem['sku_from_country']) ? '' : intval($arrListItem['sku_from_country']);
            $arrRoundResult['sku_from_country_text'] = empty($arrListItem['sku_from_country_text']) ? '' : strval($arrListItem['sku_from_country_text']);
            $arrRoundResult['sku_net'] = empty($arrListItem['sku_net']) ? '' : strval($arrListItem['sku_net']);
            $arrRoundResult['sku_net_unit'] = empty($arrListItem['sku_net_unit']) ? '' : intval($arrListItem['sku_net_unit']);
            $arrRoundResult['sku_net_unit_text'] = empty($arrListItem['sku_net_unit_text']) ? '' : strval($arrListItem['sku_net_unit_text']);
            $arrRoundResult['upc_id'] = empty($arrListItem['upc_id']) ? '' : strval($arrListItem['upc_id']);
            $arrRoundResult['upc_unit'] = empty($arrListItem['upc_unit']) ? '' : intval($arrListItem['upc_unit']);
            $arrRoundResult['upc_unit_text'] = empty($arrListItem['upc_unit_text']) ? '' : strval($arrListItem['upc_unit_text']);
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? '' : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['sku_effect_type'] = empty($arrListItem['sku_effect_type']) ? '' : intval($arrListItem['sku_effect_type']);
            $arrRoundResult['expire_date'] = empty($arrListItem['expire_date']) ? '' : strval($arrListItem['expire_date']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? '' : intval($arrListItem['stockin_order_real_amount']);
//            $arrRoundResult['sku_price'] = empty($arrListItem['sku_price']) ? '' : intval($arrListItem['sku_price']);
//            $arrRoundResult['sku_price_yuan'] = empty($arrListItem['sku_price']) ? '' : sprintf('%0.2f', intval($arrListItem['sku_price'])/100);
//            $arrRoundResult['sku_price_tax'] = empty($arrListItem['sku_price_tax']) ? '' : intval($arrListItem['sku_price_tax']);
//            $arrRoundResult['sku_price_tax_yuan'] = empty($arrListItem['sku_price_tax']) ? '' : sprintf('%0.2f', intval($arrListItem['sku_price_tax'])/100);
//            $arrRoundResult['stockin_order_sku_total_price'] = empty($arrListItem['stockin_order_sku_total_price']) ? '' : intval($arrListItem['stockin_order_sku_total_price']);
//            $arrRoundResult['stockin_order_sku_total_price_yuan'] = empty($arrListItem['stockin_order_sku_total_price']) ? '' : sprintf('%0.2f', intval($arrListItem['stockin_order_sku_total_price'])/100);
//            $arrRoundResult['stockin_order_sku_total_price_tax'] = empty($arrListItem['stockin_order_sku_total_price_tax']) ? '' : intval($arrListItem['stockin_order_sku_total_price_tax']);
//            $arrRoundResult['stockin_order_sku_total_price_tax_yuan'] = empty($arrListItem['stockin_order_sku_total_price_tax']) ? '' : sprintf('%0.2f', intval($arrListItem['stockin_order_sku_total_price_tax'])/100);

            $arrFormatResult['list'][] = $arrRoundResult;
        }
        $arrFormatResult['total'] = $arrRet['total'];
        $userId = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
        $appId = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['system'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult, $userId, $appId);

        return $arrFormatResult;
    }
}
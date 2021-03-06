<?php
/**
 * @name Action_GetStockoutStockinDetailFormApi
 * @desc 报表-获取销退入库明细（分页）Api，注释：接口只查询入库类型为 销退入库 的数据
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockoutStockinDetailFormApi extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids' => 'str|required',
        'stockin_order_id' => 'regex|patern[/^(SIO\d{13})?$/]',
        'source_order_id' => 'regex|patern[/^(SOO\d{13})?$/]',
        'sku_id' => 'int',
        'client_id' => 'str',
        'client_name' => 'str',
        'stockin_time_start' => 'int|required',
        'stockin_time_end' => 'int|required',
        'page_num' => 'int|default[1]|min[1]',
        'page_size' => 'int|required|min[1]|max[200]',
    ];

    /**
     * filter price fields
     * @var array
     */
    protected $arrPriceFields = [
        'sku_price',
        'sku_price_yuan',
        'sku_price_tax',
        'sku_price_tax_yuan',
        'stockin_order_sku_total_price',
        'stockin_order_sku_total_price_yuan',
        'stockin_order_sku_total_price_tax',
        'stockin_order_sku_total_price_tax_yuan',
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
            $arrRoundResult['stockin_order_id'] =
                empty($arrListItem['stockin_order_id']) ? ''
                    : Nscm_Define_OrderPrefix::SIO . strval($arrListItem['stockin_order_id']);
            $arrRoundResult['source_order_id'] =
                empty($arrListItem['source_order_id']) ? ''
                    : Nscm_Define_OrderPrefix::SOO . strval($arrListItem['source_order_id']);
            $arrRoundResult['city_name'] = empty($arrListItem['city_name']) ? ''
                : strval($arrListItem['city_name']);
            $arrRoundResult['city_id'] = empty($arrListItem['city_id']) ? 0
                : intval($arrListItem['city_id']);
            $arrRoundResult['warehouse_id'] = empty($arrListItem['warehouse_id']) ? 0
                : intval($arrListItem['warehouse_id']);
            $arrRoundResult['warehouse_name'] = empty($arrListItem['warehouse_name']) ? ''
                : strval($arrListItem['warehouse_name']);
            $arrRoundResult['stockin_order_type'] = empty($arrListItem['stockin_order_type']) ? 0
                : intval($arrListItem['stockin_order_type']);
            $arrRoundResult['stockin_order_type_text'] =
                Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_MAP[$arrListItem['stockin_order_type']]
                ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['stockin_time'] = empty($arrListItem['stockin_time']) ? 0
                : intval($arrListItem['stockin_time']);
            $arrRoundResult['stockin_time_text'] =
                empty($arrListItem['stockin_time_text']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrListItem['stockin_time_text']);
            $arrRoundResult['stockin_batch_id'] = empty($arrListItem['stockin_batch_id']) ? 0
                : intval($arrListItem['stockin_batch_id']);
            $arrRoundResult['stockin_order_status'] = empty($arrListItem['stockin_order_status']) ? 0
                : intval($arrListItem['stockin_order_status']);
            $arrRoundResult['stockin_order_status_text'] = empty($arrListItem['stockin_order_status_text']) ? ''
                : strval($arrListItem['stockin_order_status_text']);
            $arrRoundResult['client_name'] = empty($arrListItem['client_name']) ? ''
                : strval($arrListItem['client_name']);
            $arrRoundResult['client_id'] = empty($arrListItem['client_id']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrListItem['client_id']);
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? 0
                : intval($arrListItem['sku_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? ''
                : strval($arrListItem['sku_name']);
            $arrRoundResult['sku_category_1'] = empty($arrListItem['sku_category_1']) ? 0
                : intval($arrListItem['sku_category_1']);
            $arrRoundResult['sku_category_2'] = empty($arrListItem['sku_category_2']) ? 0
                : intval($arrListItem['sku_category_2']);
            $arrRoundResult['sku_category_3'] = empty($arrListItem['sku_category_3']) ? 0
                : intval($arrListItem['sku_category_3']);
            $arrRoundResult['sku_category_1_text'] = empty($arrListItem['sku_category_1_text']) ? ''
                : strval($arrListItem['sku_category_1_text']);
            $arrRoundResult['sku_category_2_text'] = empty($arrListItem['sku_category_2_text']) ? ''
                : strval($arrListItem['sku_category_2_text']);
            $arrRoundResult['sku_category_3_text'] = empty($arrListItem['sku_category_3_text']) ? ''
                : strval($arrListItem['sku_category_3_text']);
            $arrRoundResult['sku_from_country'] = empty($arrListItem['sku_from_country']) ? 0
                : intval($arrListItem['sku_from_country']);
            $arrRoundResult['sku_from_country_text'] =
                empty($arrListItem['sku_from_country_text']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrListItem['sku_from_country_text']);
            $arrRoundResult['sku_net'] = empty($arrListItem['sku_net']) ? '0'
                : strval($arrListItem['sku_net']);
            $arrRoundResult['sku_net_unit'] = empty($arrListItem['sku_net_unit']) ? 0
                : intval($arrListItem['sku_net_unit']);
            $arrRoundResult['sku_net_unit_text'] =
                empty($arrListItem['sku_net_unit_text']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrListItem['sku_net_unit_text']);
            $arrRoundResult['sku_net_number_unit_text'] =
                $arrRoundResult['sku_net'] . $arrRoundResult['sku_net_unit_text'];
            $arrRoundResult['upc_id'] =
                empty($arrListItem['upc_id']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrListItem['upc_id']);
            $arrRoundResult['upc_unit'] = empty($arrListItem['upc_unit']) ? 0
                : intval($arrListItem['upc_unit']);
            $arrRoundResult['upc_unit_text'] =
                empty($arrListItem['upc_unit_text']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrListItem['upc_unit_text']);
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? 0
                : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['sku_effect_type_text'] =
                Order_Define_Sku::SKU_EFFECT_TYPE_EXPIRE_MAP[$arrListItem['sku_effect_type']]
                ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['expire_date'] = empty($arrListItem['expire_date']) ? 0
                : intval($arrListItem['expire_date']);
            $arrRoundResult['expire_date_text'] =
                Order_Util::getFormatDate($arrListItem['expire_date']) ?? '';
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? 0
                : intval($arrListItem['stockin_order_real_amount']);
            $arrRoundResult['sku_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price']);
            $arrRoundResult['sku_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price_tax']);
            $arrRoundResult['stockin_order_sku_total_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['stockin_order_sku_total_price']);
            $arrRoundResult['stockin_order_sku_total_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['stockin_order_sku_total_price_tax']);
            $arrRoundResult['sku_good_amount'] = empty($arrListItem['sku_good_amount']) ? 0
                : intval($arrListItem['sku_good_amount']);
            $arrRoundResult['sku_defective_amount'] = empty($arrListItem['sku_defective_amount']) ? 0
                : intval($arrListItem['sku_defective_amount']);
            $arrRoundResult['stockin_order_remark'] = strval($arrListItem['stockin_order_remark']);

            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult['list'][] = $arrRoundResult;
        }
        $arrFormatResult['total'] = $arrRet['total'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult['list']);

        return $arrFormatResult;
    }
}
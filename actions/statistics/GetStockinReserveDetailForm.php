<?php
/**
 * @name Action_GetStockinReserveDetailForm
 * @desc 报表-获取采购入库明细（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinReserveDetailForm extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'output_type' => 'int|required|default[1]|min[0]',
        'warehouse_id' => 'str',
        'stockin_order_id' => 'regex|patern[/^(SIO\d{13})?$/]',
        'source_order_id' => 'regex|patern[/^((ASN|SOO)\d{13})?$/]',
        'sku_id' => 'int',
        'sku_category_3' => 'int',
        'vendor_id' => 'int',
        'reserve_order_plan_time_start' => 'int',
        'reserve_order_plan_time_end' => 'int',
        'stockin_time_start' => 'int',
        'stockin_time_end' => 'int',
        'page_num' => 'int|default[1]|min[1]',
        'page_size' => 'int|default[0]|min[1]|max[100]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct function
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Statistics_GetStockinReserveDetailForm();
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
            $arrRoundResult['stockin_order_id'] = empty($arrListItem['stockin_order_id']) ? '' : intval($arrListItem['stockin_order_id']);
            $arrRoundResult['source_order_id'] = empty($arrListItem['source_order_id']) ? '' : intval($arrListItem['source_order_id']);
            $arrRoundResult['city_name'] = empty($arrListItem['city_name']) ? '' : strval($arrListItem['city_name']);
            $arrRoundResult['city_id'] = empty($arrListItem['city_id']) ? '' : intval($arrListItem['city_id']);
            $arrRoundResult['vendor_name'] = empty($arrListItem['vendor_name']) ? '' : strval($arrListItem['vendor_name']);
            $arrRoundResult['vendor_id'] = empty($arrListItem['vendor_id']) ? '' : intval($arrListItem['vendor_id']);
            $arrRoundResult['stockin_order_type'] = empty($arrListItem['stockin_order_type']) ? '' : intval($arrListItem['stockin_order_type']);
            $arrRoundResult['reserve_order_plan_time_text'] = empty($arrListItem['reserve_order_plan_time_text']) ? '' : strval($arrListItem['reserve_order_plan_time_text']);
            $arrRoundResult['stockin_time_text'] = empty($arrListItem['stockin_time_text']) ? '' : strval($arrListItem['stockin_time_text']);
            $arrRoundResult['stockin_batch_id'] = empty($arrListItem['stockin_batch_id']) ? '' : intval($arrListItem['stockin_batch_id']);
            $arrRoundResult['stockin_order_status_text'] = empty($arrListItem['stockin_order_status_text']) ? '' : strval($arrListItem['stockin_order_status_text']);
            $arrRoundResult['vendor_name'] = empty($arrListItem['vendor_name']) ? '' : strval($arrListItem['vendor_name']);
            $arrRoundResult['vendor_id'] = empty($arrListItem['vendor_id']) ? '' : intval($arrListItem['vendor_id']);
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? '' : intval($arrListItem['sku_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? '' : strval($arrListItem['sku_name']);
            $arrRoundResult['sku_category_1_text'] = empty($arrListItem['sku_category_1_text']) ? '' : strval($arrListItem['sku_category_1_text']);
            $arrRoundResult['sku_category_2_text'] = empty($arrListItem['sku_category_2_text']) ? '' : strval($arrListItem['sku_category_2_text']);
            $arrRoundResult['sku_category_3_text'] = empty($arrListItem['sku_category_3_text']) ? '' : strval($arrListItem['sku_category_3_text']);
            $arrRoundResult['sku_from_country'] = empty($arrListItem['sku_from_country']) ? '' : intval($arrListItem['sku_from_country']);
            $arrRoundResult['sku_net'] = empty($arrListItem['sku_net']) ? '' : strval($arrListItem['sku_net']);
            $arrRoundResult['sku_net_unit_text'] = empty($arrListItem['sku_net_unit_text']) ? '' : strval($arrListItem['sku_net_unit_text']);
            $arrRoundResult['upc_unit_text'] = empty($arrListItem['upc_unit_text']) ? '' : strval($arrListItem['upc_unit_text']);
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? '' : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['expire_time'] = empty($arrListItem['expire_time']) ? '' : strval($arrListItem['expire_time']);
            $arrRoundResult['reserve_order_plan_amount'] = empty($arrListItem['reserve_order_plan_amount']) ? '' : intval($arrListItem['reserve_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? '' : intval($arrListItem['stockin_order_real_amount']);
            $arrRoundResult['sku_price'] = empty($arrListItem['sku_price']) ? '' : intval($arrListItem['sku_price']);
            $arrRoundResult['sku_price_tax'] = empty($arrListItem['sku_price_tax']) ? '' : intval($arrListItem['sku_price_tax']);
            $arrRoundResult['stockin_order_sku_total_price'] = empty($arrListItem['stockin_order_sku_total_price']) ? '' : intval($arrListItem['stockin_order_sku_total_price']);
            $arrRoundResult['stockin_order_sku_total_price_tax'] = empty($arrListItem['stockin_order_sku_total_price_tax']) ? '' : intval($arrListItem['stockin_order_sku_total_price_tax']);

            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];

        return $arrFormatResult;
    }
}
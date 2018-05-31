<?php
/**
 * @name Action_GetReserveOrderSkuList
 * @desc 获取预约订单商品列表（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderSkuList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'regex|patern[/^ASN\d{13}$/]',
        'reserve_order_status' => 'int|default[0]',
        'page_num' => 'int|default[1]|min[1]|optional',
        'page_size' => 'int|required|min[0]|max[200]',
    ];

    /**
     * filter price fields
     * @var array
     */
    protected $arrPriceFields = [
        'sku_price_yuan',
        'sku_price_tax_yuan',
        'reserve_order_sku_total_price_yuan',
        'reserve_order_sku_total_price_tax_yuan',
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
        $this->objPage = new Service_Page_Reserve_GetReserveOrderSkuList();
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
        if (empty($arrRet['list'])) {
            return $arrFormatResult;
        }
        $arrRetList = $arrRet['list'];
        $boolHideCount = $this->hideCount();
        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['upc_id'] = empty($arrListItem['upc_id']) ? ''
                : strval($arrListItem['upc_id']);
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? 0
                : intval($arrListItem['sku_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? ''
                : strval($arrListItem['sku_name']);
            $arrRoundResult['upc_unit'] = empty($arrListItem['upc_unit']) ? 0
                : intval($arrListItem['upc_unit']);
            $arrRoundResult['upc_unit_text'] =
                Nscm_Define_Sku::UPC_UNIT_MAP[intval($arrListItem['upc_unit'])]
                ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? 0
                : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['sku_net'] = empty($arrListItem['sku_net']) ? ''
                : strval($arrListItem['sku_net']);
            $arrRoundResult['sku_net_unit'] = empty($arrListItem['sku_net_unit']) ? 0
                : intval($arrListItem['sku_net_unit']);
            $arrRoundResult['sku_net_unit_text'] =
                Order_Define_Sku::SKU_NET_MAP[intval($arrListItem['sku_net_unit'])]
                ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['sku_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price']);
            $arrRoundResult['sku_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price_tax']);
            $arrRoundResult['reserve_order_sku_total_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['reserve_order_sku_total_price']);
            $arrRoundResult['reserve_order_sku_total_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['reserve_order_sku_total_price_tax']);
            $arrRoundResult['reserve_order_sku_plan_amount'] =
                empty($arrListItem['reserve_order_sku_plan_amount']) ? 0
                    : intval($arrListItem['reserve_order_sku_plan_amount']);
            $arrRoundResult['stockin_order_sku_real_amount'] = $boolHideCount ? '--'
                : intval($arrListItem['stockin_order_sku_real_amount']);
            $arrRoundResult['stockin_order_sku_extra_info'] =
                empty($arrListItem['stockin_order_sku_extra_info']) ? ''
                    : strval($arrListItem['stockin_order_sku_extra_info']);
            $arrRoundResult['upc_min_unit'] = intval($arrListItem['upc_min_unit']);
            $arrRoundResult['upc_min_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$arrListItem['upc_min_unit']] ?? '';
            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult['list']);

        return $arrFormatResult;
    }

    /**
     * return - replace count
     * @return bool
     */
    private function hideCount()
    {
        return boolval(isset(Order_Define_ReserveOrder::TRANS_NULL_TO[$this->arrFilterResult['reserve_order_status']]));
    }
}
<?php
/**
 * @name Action_GetStockinOrderSkuList
 * @desc 获取入库单商品列表（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinOrderSkuList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_id' => 'regex|patern[/^SIO\d{13}$/]',
        'page_num' => 'int|default[1]|min[1]|optional',
        'page_size' => 'int|required|min[0]|max[200]',
    ];

    /**
     * filter price fields
     * @var array
     */
    protected $arrPriceFields = [
        'stockin_order_sku_total_price_tax_yuan',
        'stockin_order_sku_total_price_yuan',
        'sku_price_yuan',
        'sku_price_tax_yuan',
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
        $this->objPage = new Service_Page_Stockin_GetStockinOrderSkuList();
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
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? 0
                : intval($arrListItem['sku_id']);
            $arrRoundResult['upc_id'] = empty($arrListItem['upc_id']) ? 0
                : strval($arrListItem['upc_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? ''
                : strval($arrListItem['sku_name']);
            $arrRoundResult['upc_unit'] = empty($arrListItem['upc_unit']) ? 0
                : intval($arrListItem['upc_unit']);
            $arrRoundResult['upc_unit_text'] =
                empty($arrListItem['upc_unit']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : Nscm_Define_Sku::UPC_UNIT_MAP[$arrListItem['upc_unit']]
                    ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? 0
                : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['stockin_order_sku_total_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['stockin_order_sku_total_price_tax']);
            $arrRoundResult['stockin_order_sku_total_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['stockin_order_sku_total_price']);
            $arrRoundResult['sku_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price']);
            $arrRoundResult['sku_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrListItem['sku_price_tax']);
            $arrRoundResult['reserve_order_sku_plan_amount'] = empty($arrListItem['reserve_order_sku_plan_amount']) ? 0
                : intval($arrListItem['reserve_order_sku_plan_amount']);
            $arrRoundResult['stockin_order_sku_real_amount'] = empty($arrListItem['stockin_order_sku_real_amount']) ? 0
                : intval($arrListItem['stockin_order_sku_real_amount']);
            $arrRoundResult['upc_min_unit'] = empty($arrListItem['upc_min_unit']) ? 0
                : intval($arrListItem['upc_min_unit']);
            $arrRoundResult['upc_min_unit_text'] =
                empty($arrListItem['upc_min_unit']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : (Order_Define_Sku::UPC_UNIT_MAP[$arrListItem['upc_min_unit']]
                    ?? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR);
            $arrRoundResult['sku_main_image'] = empty($arrListItem['sku_main_image'])
                ? Order_Define_Sku::SKU_IMAGE_DEFAULT_URL
                : strval($arrListItem['sku_main_image']);
            // 数据库存放的stockin_order_sku_extra_info是json编码的Unix时间戳，转为文本形式时间给FE
            $arrSkuExtInf = json_decode($arrListItem['stockin_order_sku_extra_info'], true) ?? [];
            foreach ($arrSkuExtInf as $item => $value) {
                if (isset($value['expire_date'])) {
                    $arrSkuExtInf[$item]['expire_date'] = Order_Util::getFormatDate(intval($value['expire_date']));
                }
            }
            $arrRoundResult['stockin_order_sku_extra_info'] = empty($arrSkuExtInf) ? '' : json_encode($arrSkuExtInf);

            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult['list']);
        return $arrFormatResult;
    }
}
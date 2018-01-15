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
            $arrRoundResult['sku_id'] = empty($arrListItem['sku_id']) ? '' : intval($arrListItem['sku_id']);
            $arrRoundResult['upc_id'] = empty($arrListItem['upc_id']) ? '' : strval($arrListItem['upc_id']);
            $arrRoundResult['sku_name'] = empty($arrListItem['sku_name']) ? '' : strval($arrListItem['sku_name']);
            $arrRoundResult['upc_unit'] = empty($arrListItem['upc_unit']) ? '' : intval($arrListItem['upc_unit']);
            $arrRoundResult['upc_unit_num'] = empty($arrListItem['upc_unit_num']) ? '' : intval($arrListItem['upc_unit_num']);
            $arrRoundResult['stockin_order_sku_total_price_tax'] = empty($arrListItem['stockin_order_sku_total_price_tax']) ? '' : intval($arrListItem['stockin_order_sku_total_price_tax']);
            $arrRoundResult['stockin_order_sku_total_price'] = empty($arrListItem['stockin_order_sku_total_price']) ? '' : intval($arrListItem['stockin_order_sku_total_price']);
            $arrRoundResult['sku_price'] = empty($arrListItem['sku_price']) ? '' : intval($arrListItem['sku_price']);
            $arrRoundResult['sku_price_tax'] = empty($arrListItem['sku_price_tax']) ? '' : intval($arrListItem['sku_price_tax']);
            $arrRoundResult['reserve_order_sku_plan_amount'] = empty($arrListItem['reserve_order_sku_plan_amount']) ? '' : intval($arrListItem['reserve_order_sku_plan_amount']);
            $arrRoundResult['stockin_order_sku_real_amount'] = empty($arrListItem['stockin_order_sku_real_amount']) ? '' : intval($arrListItem['stockin_order_sku_real_amount']);
            $arrRoundResult['stockin_order_sku_extra_info'] = empty($arrListItem['stockin_order_sku_extra_info']) ? '' : strval($arrListItem['stockin_order_sku_extra_info']);

            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];

        return $arrFormatResult;
    }
}
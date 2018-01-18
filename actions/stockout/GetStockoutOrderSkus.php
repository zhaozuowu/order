<?php
/**
* @name Action_GetStockoutOrderSkus
* @desc 查询出库单商品列表
* @author  jinyu02@iwaimai.baidu.com
*/

class Action_GetStockoutOrderSkus extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required',
        'stockout_order_id' => 'str|required',
        'sku_id' => 'str',
        'upc_id' => 'str',
        'sku_name' => 'str',
    ];

    /**
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;


    /**
     * init object
     */
    public function myConstruct() {
        $this->objPage = new Service_Page_Stockout_GetStockoutOrderSkus();
    }


    /**
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet) {
        $arrFormatRet = [];
        $arrFormatRet['total'] = $arrRet['total'];
        foreach ((array)$arrRet['skus'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['sku_name'] = empty($arrRetItem['sku_name']) ? '' : $arrRetItem['sku_name'];
            $arrFormatRetItem['sku_id'] = empty($arrRetItem['sku_id']) ? 0 : $arrRetItem['sku_id'];
            $arrFormatRetItem['upc_id'] = empty($arrRetItem['upc_id']) ? '' : $arrRetItem['upc_id'];
            $arrFormatRetItem['sku_category'] = empty($arrRetItem['sku_category']) ? 0 : $arrRetItem['sku_category'];
            $arrFormatRetItem['sku_category_text'] = empty($arrRetItem['sku_category_text']) ? '' : $arrRetItem['sku_category_text'];
            $arrFormatRetItem['cost_price'] = empty($arrRetItem['cost_price']) ? 0 : Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_price']);
            $arrFormatRetItem['cost_price_tax'] = empty($arrRetItem['cost_price_tax']) ? 0 : Nscm_Service_Price::convertDefaultToYuan($arrRetItem['cost_price_tax']);
            $arrFormatRetItem['sku_tax_rate'] = empty($arrRetItem['sku_tax_rate']) ? 0 : Order_Define_Sku::SKU_TAX_RATE[$arrRetItem['sku_tax_rate']];
            $arrFormatRetItem['upc_unit'] = empty($arrRetItem['upc_unit']) ? 0 : $arrRetItem['upc_unit'];
            $arrFormatRetItem['upc_unit_text'] = empty($arrRetItem['upc_unit']) ? '' : Order_Define_Sku::UPC_UNIT_MAP[$arrRetItem['upc_unit']];
            $arrFormatRetItem['upc_unit_num'] = empty($arrRetItem['upc_unit_num']) ? 0 : $arrRetItem['upc_unit_num'];
            $arrFormatRetItem['upc_unit_num_text'] = empty($arrRetItem['upc_unit_num']) ? '' : '1*'.$arrRetItem['upc_unit_num'];
            $arrFormatRetItem['sku_net'] = empty($arrRetItem['sku_net']) ? '' : $arrRetItem['sku_net'];
            $arrFormatRetItem['pickup_amount'] = empty($arrRetItem['pickup_amount']) ? 0 : $arrRetItem['pickup_amount'];
            $arrFormatRet['skus'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }
}
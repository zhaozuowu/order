<?php
/**
 * @name Action_GetDistributionSkuList
 * @desc 查询配货商品列表
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetDistributionSkuList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required|max[200]',
        'warehouse_id' => 'int|required',
        'sku_ids' => 'str|required',
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
        $this->objPage = new Service_Page_Stockout_GetDistributionSkuList();
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
            $arrFormatRetItem['sku_id'] = empty($arrRetItem['sku_id']) ?  0 : intval($arrRetItem['sku_id']);
            $arrFormatRetItem['upc_id'] = empty($arrRetItem['upc_id']) ? '' : $arrRetItem['upc_id'];
            $arrFormatRetItem['sku_name'] = empty($arrRetItem['sku_name']) ? '' : $arrRetItem['sku_name'];
            $skuNeText = isset(Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]) ? Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]:'';
            $arrFormatRetItem['sku_net'] = $arrRetItem['sku_net'].$skuNeText;
            $arrFormatRetItem['upc_unit_num'] = empty($arrRetItem['upc_unit_num']) ? '' : '1*' . $arrRetItem['upc_unit_num'];
            $arrFormatRetItem['upc_unit'] = isset(Order_Define_StockoutOrder::UPC_UNIT[$arrRetItem['upc_unit']]) ? Order_Define_StockoutOrder::UPC_UNIT[$arrRetItem['upc_unit']]:'';
            $arrFormatRetItem['available_amount'] = empty($arrRetItem['available_amount']) ? 0:$arrRetItem['available_amount'];
            $arrFormatRet['list'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

}
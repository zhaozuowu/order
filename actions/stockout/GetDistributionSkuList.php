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
        'warehouse_id' => 'int|required',
        'ids' => 'str|required',
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
        $arrFormatRet['list'] = [];
        $arrFormatRet['message'] = '';
        $arrFormatTemp['sku_upc_ids'] = '';
        foreach((array)$arrRet['list'] as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['sku_id'] = empty($arrRetItem['sku_id']) ?  0 : intval($arrRetItem['sku_id']);
            $arrFormatRetItem['upc_ids'] = empty($arrRetItem['upc_ids']) ? [] : $arrRetItem['upc_ids'];
            $arrFormatRetItem['min_upc_id'] = empty($arrRetItem['min_upc_id']) ? 0 : $arrRetItem['min_upc_id'];
            $arrFormatRetItem['sku_name'] = empty($arrRetItem['sku_name']) ? '' : $arrRetItem['sku_name'];
            $skuNeText = isset(Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]) ? Order_Define_Sku::SKU_NET_MAP[$arrRetItem['sku_net_unit']]:'';
            $arrFormatRetItem['sku_net'] = $arrRetItem['sku_net'].$skuNeText;
            $arrFormatRetItem['upc_unit_num'] = empty($arrRetItem['upc_unit_num']) ? '' : '1*' . $arrRetItem['upc_unit_num'];
            $arrFormatRetItem['upc_unit'] = isset(Nscm_Define_Sku::UPC_UNIT_MAP[$arrRetItem['upc_unit']]) ? Nscm_Define_Sku::UPC_UNIT_MAP[$arrRetItem['upc_unit']]:'';
            $arrFormatRetItem['available_amount'] = empty($arrRetItem['available_amount']) ? 0:$arrRetItem['available_amount'];
            $arrFormatRet['list'][] = $arrFormatRetItem;
            if(count($arrFormatRetItem['upc_ids']) >=Order_Define_StockoutOrder::UPC_IDS_NUM_TWO) {
                $arrFormatTemp['sku_upc_ids'].= "(".implode(",",$arrFormatRetItem['upc_ids']).")".' ';
            }
        }
        if(!empty($arrFormatTemp['sku_upc_ids'])) {
            $arrFormatRet['message'] = '"以下条码组在彩云中为相同商品'.$arrFormatTemp['sku_upc_ids'].',已为您合并出库数量；如需分别处理，请在彩云中修改"';
        }
        return $arrFormatRet;
    }

}
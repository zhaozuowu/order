<?php
/**
 * @name Action_GetPrintList
 * @desc 打印列表
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_GetSkuPrintList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'order_ids' => 'str|required',
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
        $this->objPage = new Service_Page_Stockout_GetSkuPrintList();
    }

    /**
     * format result
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet) {
        $arrFormatRet = [];
        $arrFormatRet['order_amount'] = empty($arrRet['order_amount']) ?  0 : $arrRet['order_amount'];
        $arrFormatRet['pickup_amount'] = empty($arrRet['pickup_amount']) ? '' : $arrRet['customer_address'];
        $arrFormatRet['print_time'] = time();
        $arrFormatRet['skus'] = $this->formatSku($arrRet['skus']);
        $arrFormatRet['operator'] = empty($arrRet['operator']) ? '' : $arrRet['operator'];
        $arrFormatRet['pickup_date'] = empty($arrRet['update_time']) ? '' : date("Y-m-d", $arrRet['update_time']);  
        return $arrFormatRet;        
    }

    /**
     *format sku result
     * @param array $arrSkus
     * @return array
     */
    public function formatSku($arrSkus) {
        $arrFormatSkus = [];
        if (empty($arrSkus)) {
            return $arrFormatSkus;
        }
        foreach($arrSkus as $arrSkuItem) {
            $arrFormatSkuItem = [];
            $arrFormatSkuItem['upc_id'] = empty($arrSkuItem['upc_id']) ? '' : $arrSkuItem['upc_id'];
            $arrFormatSkuItem['sku_name'] = empty($arrSkuItem['sku_name']) ? '' : $arrSkuItem['sku_name'];
            $arrFormatSkuItem['sku_net'] = empty($arrSkuItem['sku_net']) ? '' : $arrSkuItem['sku_net'];
            $arrFormatSkuItem['upc_unit_text'] = empty($arrSkuItem['upc_unit']) ? 0 : $arrSkuItem['upc_unit'];
            $arrFormatSkuItem['pickup_amount'] = empty($arrSkuItem['pickup_amount']) ? 0 : $arrSkuItem['pickup_amount'];
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;
    }

}
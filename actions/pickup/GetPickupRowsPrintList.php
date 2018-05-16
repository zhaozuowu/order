<?php
/**
 * @name Action_GetPickupRowsPrintList
 * @desc 拣货单排线打印
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetPickupRowsPrintList extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'pickup_order_id' => 'int|required',
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
        $this->objPage = new Service_Page_Pickup_GetPickupRowsPrintList();

        
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {

        $arrRet = [];
        if (empty($data)) {
            return $arrRet;
        }
        foreach ($data as $key=>$item)
        {
            $arrRet['list'][]  = [
                'pickup_tms_snapshoot_num' => $key,
                'stockout_order_num' => count($item),
                'stockout_order_list' => $this->formatStockoutList($item),
            ];
        }
        return $arrRet;
    }

    private function formatStockoutList($stockoutList)
    {
        $arrFormatRet = [];
        foreach ((array)$stockoutList as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : Nscm_Define_OrderPrefix::SOO.$arrRetItem['stockout_order_id'];
            $arrFormatRetItem['logistics_order_id'] = empty($arrRetItem['logistics_order_id']) ? '' : $arrRetItem['logistics_order_id'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? '' : $arrRetItem['customer_id'];
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '' : $arrRetItem['customer_name'];
            $arrFormatRetItem['customer_contactor'] = empty($arrRetItem['customer_contactor']) ? '' : $arrRetItem['customer_contactor'];
            $arrFormatRetItem['customer_contact'] = empty($arrRetItem['customer_contact']) ? '' : $arrRetItem['customer_contact'];
            $arrFormatRetItem['customer_address'] = empty($arrRetItem['customer_address']) ? '' : $arrRetItem['customer_address'];
            //$arrFormatRetItem['devices'] = empty($arrRetItem['devices']) ? '' : $arrRetItem['devices'];

            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type']) ?
                '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrRetItem['stockout_order_type']];
            $arrFormatRetItem['stockout_order_remark'] = empty($arrRetItem['stockout_order_remark']) ? '' : $arrRetItem['customer_address'];
            $arrFormatRetItem['stockout_order_skuinfo'] =  $this->formatSkuList($arrRetItem['stockout_order_skuinfo']);
            $arrFormatRet[] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

    private function formatSkuList($skuList)
    {
        $list = [];
        foreach ($skuList as $arrItem) {
            $arrFormatItem = [];
            $arrFormatItem['sku_id'] = empty($arrItem['sku_id']) ? '' : $arrItem['sku_id']."";
            $arrFormatItem['upc_id'] = empty($arrItem['upc_id']) ? '' : $arrItem['upc_id'];
            $arrFormatItem['sku_name'] = empty($arrItem['sku_name']) ? '' : $arrItem['sku_name'];
            $skuNeText = isset(Order_Define_Sku::SKU_NET_MAP[$arrItem['sku_net_unit']]) ? Order_Define_Sku::SKU_NET_MAP[$arrItem['sku_net_unit']]:'';
            $arrFormatItem['sku_net'] = $arrItem['sku_net'].$skuNeText;
            $arrFormatItem['upc_unit_num'] = empty($arrItem['upc_unit_num']) ? '' : '1*' . $arrItem['upc_unit_num'];
            $arrFormatItem['upc_unit'] = isset(Order_Define_StockoutOrder::UPC_UNIT[$arrItem['upc_unit']]) ? Order_Define_StockoutOrder::UPC_UNIT[$arrItem['upc_unit']]:'';
            $arrFormatItem['order_amount'] = empty($arrItem['order_amount']) ? 0 : $arrItem['order_amount'];
            $arrFormatItem['distribute_amount'] = empty($arrItem['distribute_amount']) ? 0 : $arrItem['distribute_amount'];
            $arrFormatItem['pickup_amount'] = empty($arrItem['pickup_amount']) ? 0 : $arrItem['pickup_amount'];
            $list[] = $arrFormatItem;
        }
        return $list;
    }

}
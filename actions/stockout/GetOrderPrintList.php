<?php
/**
 * @name Action_GetPrintList
 * @desc 打印列表
 * @author  jinyu02@iwaimai.baidu.com
 */

class Action_GetOrderPrintList extends Order_Base_Action
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
        $this->objPage = new Service_Page_Stockout_GetOrderPrintList();
    }

    /**
     * format result
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet) {
        $arrFormatRet = [];
        foreach((array)$arrRet as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['stockout_order_id'] = empty($arrRetItem['stockout_order_id']) ?  '' : Nscm_Define_OrderPrefix::SOO.$arrRetItem['stockout_order_id'];
            $arrShelfInfo = json_decode($arrRetItem['shelf_info'], true);
            $arrFormatRetItem['supply_type_text'] = empty($arrShelfInfo['supply_type']) ?
                                                        '' : Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrShelfInfo['supply_type']];
            $arrFormatRetItem['devices'] = Order_Define_Format::formatDevices($arrShelfInfo['devices']);
            $arrFormatRetItem['executor'] = empty($arrRetItem['executor']) ? '' : $arrRetItem['executor'];
            $arrFormatRetItem['executor_contact'] = empty($arrRetItem['executor_contact']) ? '' : $arrRetItem['executor_contact'];
            $arrFormatRetItem['stockout_order_type'] = empty($arrRetItem['stockout_order_type']) ? 0 : $arrRetItem['stockout_order_type'];
            $arrFormatRetItem['stockout_order_type_text'] = empty($arrRetItem['stockout_order_type']) ? 
                                                                '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrRetItem['stockout_order_type']];
            $arrFormatRetItem['business_form_order_id'] = empty($arrRetItem['business_form_order_id']) ? 0 : $arrRetItem['business_form_order_id'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? '' : $arrRetItem['customer_id'];
            $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '' : $arrRetItem['customer_name'];
            $arrFormatRetItem['customer_contactor'] = empty($arrRetItem['customer_contactor']) ? '' : $arrRetItem['customer_contactor'];
            $arrFormatRetItem['customer_contact'] = empty($arrRetItem['customer_contact']) ? '' : $arrRetItem['customer_contact'];
            $arrFormatRetItem['customer_address'] = empty($arrRetItem['customer_address']) ? '' : $arrRetItem['customer_address'];
            $arrFormatRetItem['operator'] = empty($arrRetItem['operator']) ? '' : $arrRetItem['operator'];
            $arrFormatRetItem['pickup_date'] = empty($arrRetItem['update_time']) ? '' : date("Y-m-d", $arrRetItem['update_time']);
            $arrFormatRetItem['stockout_order_remark'] = empty($arrRetItem['stockout_order_remark']) ? '' : $arrRetItem['stockout_order_remark'];
            $arrFormatRetItem['skus'] = empty($arrRetItem['skus']) ? [] : $this->formatSku($arrRetItem['skus'], $arrRetItem['stockout_order_status']);
            $arrFormatRet['list'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;        
    }

    /**
     *format sku result
     * @param array $arrSkus
     * @param integer $intStatus
     * @return array
     */
    public function formatSku($arrSkus, $intStatus) {
        $arrFormatSkus = [];
        if (empty($arrSkus)) {
            return $arrFormatSkus;
        }
        foreach($arrSkus as $arrSkuItem) {
            $arrFormatSkuItem = [];
            $arrFormatSkuItem['sku_id'] = empty($arrSkuItem['sku_id']) ? 0 : $arrSkuItem['sku_id'];
            $arrFormatSkuItem['upc_id'] = empty($arrSkuItem['upc_id']) ? '' : $arrSkuItem['upc_id'];
            $arrFormatSkuItem['sku_name'] = empty($arrSkuItem['sku_name']) ? '' : $arrSkuItem['sku_name'];
            $arrFormatSkuItem['distribute_amount'] = empty($arrSkuItem['distribute_amount']) ? 0 : $arrSkuItem['distribute_amount'];
            $arrFormatSkuItem['sku_net'] = empty($arrSkuItem['sku_net']) ?
                                            '' : ($arrSkuItem['sku_net'] . Order_Define_Sku::SKU_NET_MAP[$arrSkuItem['sku_net_unit']]);
            $arrFormatSkuItem['upc_unit_text'] = empty(Nscm_Define_Sku::UPC_UNIT_MAP[$arrSkuItem['upc_unit']]) ?
                                                    0 : Nscm_Define_Sku::UPC_UNIT_MAP[$arrSkuItem['upc_unit']];
            if ($intStatus >= Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS) {
                $arrFormatSkuItem['pickup_amount'] = empty($arrSkuItem['pickup_amount']) ? 0 : $arrSkuItem['pickup_amount'];
            } else {
                $arrFormatSkuItem['pickup_amount'] = '';
            }
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;
    }
}
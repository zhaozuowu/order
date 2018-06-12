<?php
/**
 * @name Action_GetReserveOrderPrintList
 * @desc 预约入库单打印
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Action_GetReserveOrderPrintList extends Order_Base_Action
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

    /**n
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Reserve_GetReserveOrderPrintList();
    }

    /**
     * format result
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet) {
        $arrFormatRet = [];
        foreach($arrRet as $arrRetItem) {
            $arrFormatRetItem = [];
            $boolHideRealAmount = isset(Order_Define_ReserveOrder::TRANS_NULL_TO[$arrRetItem['reserve_order_status']]);
            $arrFormatRetItem['reserve_order_id'] = empty($arrRetItem['reserve_order_id']) ?  '' : Nscm_Define_OrderPrefix::ASN.$arrRetItem['reserve_order_id'];
            $arrFormatRetItem['purchase_order_id'] = empty($arrRetItem['purchase_order_id']) ? 0 : Nscm_Define_OrderPrefix::PUR.$arrRetItem['purchase_order_id'];
            $arrFormatRetItem['vendor_name'] = empty($arrRetItem['vendor_name']) ? '' : $arrRetItem['vendor_name'];
            $arrFormatRetItem['vendor_id'] = empty($arrRetItem['vendor_id']) ? 0 : $arrRetItem['vendor_id'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['warehouse_contact'] = empty($arrRetItem['warehouse_contact']) ? '' : $arrRetItem['warehouse_contact'];
            $arrFormatRetItem['warehouse_contact_phone'] = empty($arrRetItem['warehouse_contact_phone']) ? '' : $arrRetItem['warehouse_contact_phone'];
            $arrFormatRetItem['reserve_order_remark'] = empty($arrRetItem['reserve_order_remark']) ? '' : $arrRetItem['reserve_order_remark'];
            $arrFormatRetItem['stockin_order_real_amount'] = $boolHideRealAmount ? '' : $arrRetItem['stockin_order_real_amount'];
            $arrFormatRetItem['sign_date'] = empty($arrRetItem['sign_date']) ? '' : $arrRetItem['sign_date'];
            $arrFormatRetItem['skus'] = empty($arrRetItem['skus']) ? [] : $this->formatSku($arrRetItem['skus'], $boolHideRealAmount);
            $arrFormatRet['list'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

    /**
     *format sku result
     * @param array $arrSkus
     * @param bool $boolHideRealAmount
     * @return array
     */
    public function formatSku($arrSkus, $boolHideRealAmount) {
        $arrFormatSkus = [];
        if (empty($arrSkus)) {
            return $arrFormatSkus;
        }
        foreach($arrSkus as $arrSkuItem) {
            $arrFormatSkuItem = [];
            $arrFormatSkuItem['upc_id'] = empty($arrSkuItem['upc_id']) ? '' : $arrSkuItem['upc_id'];
            $arrFormatSkuItem['sku_name'] = empty($arrSkuItem['sku_name']) ? '' : $arrSkuItem['sku_name'];
            $arrFormatSkuItem['sku_net'] = empty($arrSkuItem['sku_net']) ? '' : $arrSkuItem['sku_net'];
            $skuNeText = isset(Order_Define_Sku::SKU_NET_MAP[$arrSkuItem['sku_net_unit']]) ? Order_Define_Sku::SKU_NET_MAP[$arrSkuItem['sku_net_unit']]:'';
            $arrFormatSkuItem['sku_net_text'] = $arrFormatSkuItem['sku_net'].$skuNeText;
            $arrFormatSkuItem['upc_unit_text'] = empty($arrSkuItem['upc_unit']) ? '' : Nscm_Define_Sku::UPC_UNIT_MAP[$arrSkuItem['upc_unit']];
            $arrFormatSkuItem['upc_min_unit_text'] = empty($arrSkuItem['upc_min_unit']) ? '' : Nscm_Define_Sku::UPC_UNIT_MAP[$arrSkuItem['upc_min_unit']];
            $arrFormatSkuItem['plan_amount'] = empty($arrSkuItem['reserve_order_sku_plan_amount']) ? 0 : $arrSkuItem['reserve_order_sku_plan_amount'];
            $arrFormatSkuItem['real_amount'] = $boolHideRealAmount ? '' : $arrSkuItem['stockin_order_sku_real_amount'];
            $stockin_order_sku_extra_info = json_decode($arrSkuItem['stockin_order_sku_extra_info'],true);
            $arrFormatSkuItem['expire_date'] = $this->formatExpireDate($stockin_order_sku_extra_info);
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;
    }

    /**
     * @param $extrInfo
     * @return array
     */
    private function formatExpireDate($extrInfo)
    {
        $list = [];
        if (empty($extrInfo)) {
            return [];
        }
        foreach ($extrInfo as $itemInfo)
        {
            $list[] = empty($itemInfo['expire_date']) ? '' : date('Y-m-d',$itemInfo['expire_date']);
        }
        return $list;
    }


}
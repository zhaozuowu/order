<?php
/**
 * @name Action_GetReserveOrderPrintList
 * @desc 预约入库单打印
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Action_GetReserveOrderPrintList extends Order_Base_Action
{
    /**
     * 是否验证登陆
     * @var boolean
     */
    protected $boolCheckLogin = false;

    /**
     * 判断是否有权限
     *
     * @var boolean
     */
    protected $boolCheckAuth = false;

    /**
     * 是否校内网IP
     *
     * @var boolean
     */
   // protected $boolCheckIp = false;
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
            $arrFormatRetItem['reserve_order_id'] = empty($arrRetItem['reserve_order_id']) ?  '' : Nscm_Define_OrderPrefix::ASN.$arrRetItem['reserve_order_id'];
            $arrFormatRetItem['purchase_order_id'] = empty($arrRetItem['purchase_order_id']) ? 0 : Nscm_Define_OrderPrefix::PUR.$arrRetItem['purchase_order_id'];
            $arrFormatRetItem['vendor_name'] = empty($arrRetItem['vendor_name']) ? '' : $arrRetItem['vendor_name'];
            $arrFormatRetItem['vendor_id'] = empty($arrRetItem['vendor_id']) ? 0 : $arrRetItem['vendor_id'];
            $arrFormatRetItem['warehouse_name'] = empty($arrRetItem['warehouse_name']) ? '' : $arrRetItem['warehouse_name'];
            $arrFormatRetItem['warehouse_contact'] = empty($arrRetItem['warehouse_contact']) ? '' : $arrRetItem['warehouse_contact'];
            $arrFormatRetItem['warehouse_contact_phone'] = empty($arrRetItem['warehouse_contact_phone']) ? '' : $arrRetItem['warehouse_contact_phone'];
            $arrFormatRetItem['reserve_order_remark'] = empty($arrRetItem['reserve_order_remark']) ? '' : $arrRetItem['reserve_order_remark'];
            $arrFormatRetItem['stockin_order_real_amount'] = empty($arrRetItem['stockin_order_real_amount']) ? 0 : $arrRetItem['stockin_order_real_amount'];
            $arrFormatRetItem['sign_date'] = empty($arrRetItem['sign_date']) ? '' : $arrRetItem['sign_date'];
            $arrFormatRetItem['skus'] = empty($arrRetItem['skus']) ? [] : $this->formatSku($arrRetItem['skus']);
            $arrFormatRetItem['skus'] = empty($arrRetItem['skus']) ? [] : $this->formatSku($arrRetItem['skus']);
            $arrFormatRet['list'][] = $arrFormatRetItem;
        }
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
            $arrFormatSkuItem['upc_unit_text'] = empty($arrSkuItem['upc_unit']) ? '' : Order_Define_Sku::UPC_UNIT_MAP[$arrSkuItem['upc_unit']];
            $arrFormatSkuItem['plan_amount'] = empty($arrSkuItem['reserve_order_sku_plan_amount']) ? 0 : $arrSkuItem['reserve_order_sku_plan_amount'];
            $arrFormatSkuItem['real_amount'] = empty($arrSkuItem['stockin_order_sku_real_amount']) ? 0 : $arrSkuItem['stockin_order_sku_real_amount'];
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;
    }


}
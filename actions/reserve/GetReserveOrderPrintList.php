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
            $arrFormatRetItem['skus'] = empty($arrRetItem['skus']) ? [] : $arrRetItem['skus'];
            $arrFormatRet['list'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;        
    }


}
<?php
/**
 * @name Action_GeReserveOrderDetail
 * @desc 查询采购单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GeReserveOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'regex|patern[/^(ASN\d{13})?$/]|required',
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
        $this->objPage = new Service_Page_Reserve_GetReserveOrderDetail();
    }

    /**
     * format result, output data format process
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        // 格式化数据结果
        $arrFormatResult = [];

        if(!empty($arrRet)) {
            $arrRoundResult = [];
            $arrRoundResult['reserve_order_id'] = empty($arrRet['reserve_order_id']) ? '' : Nscm_Define_OrderPrefix::ASN . intval($arrRet['reserve_order_id']);
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? '' : Nscm_Define_OrderPrefix::SIO . strval($arrRet['stockin_order_id']);
            $arrRoundResult['warehouse_name'] = empty($arrRet['warehouse_name']) ? '' : strval($arrRet['warehouse_name']);
            $arrRoundResult['reserve_order_plan_time'] = empty($arrRet['reserve_order_plan_time']) ? '' : intval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_status'] = empty($arrRet['reserve_order_status']) ? '' : intval($arrRet['reserve_order_status']);
            $arrRoundResult['reserve_order_remark'] = empty($arrRet['reserve_order_remark']) ? '' : strval($arrRet['reserve_order_remark']);
            $arrRoundResult['vendor_name'] = empty($arrRet['vendor_name']) ? '' : strval($arrRet['vendor_name']);
            $arrRoundResult['vendor_contactor'] = empty($arrRet['vendor_contactor']) ? '' : strval($arrRet['vendor_contactor']);
            $arrRoundResult['vendor_mobile'] = empty($arrRet['vendor_mobile']) ? '' : strval($arrRet['vendor_mobile']);
            $arrRoundResult['vendor_address'] = empty($arrRet['vendor_address']) ? '' : strval($arrRet['vendor_address']);

            $arrFormatResult = $arrRoundResult;
        }

        return $arrFormatResult;
    }
}
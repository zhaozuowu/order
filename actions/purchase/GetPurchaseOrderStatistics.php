<?php
/**
 * @name Action_GetPurchaseOrderStatistics
 * @desc Action GetPurchaseOrderStatistics 获取采购单状态统计
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetPurchaseOrderStatistics extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [];

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
        $this->objPage = new Service_Page_Purchase_GetPurchaseOrderStatistics();
    }

    /**
     * format result, output data format process
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        // 返回结果数据
        if (empty($arrRet['list'])) {
            return $arrRet;
        }

        $arrRetList = $arrRet['list'];
        $arrFormatResult = [];

        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['vendor_name'] = empty($arrListItem['vendor_name']) ? '' : strval($arrListItem['vendor_name']);
            $arrRoundResult['purchase_order_id'] = empty($arrListItem['purchase_order_id']) ? '' : Nscm_Define_OrderPrefix::PUR . intval($arrListItem['purchase_order_id']);
            $arrRoundResult['stockin_order_id'] = empty($arrListItem['stockin_order_id']) ? '' : Nscm_Define_OrderPrefix::ASN . strval($arrListItem['stockin_order_id']);
            $arrRoundResult['purchase_order_status'] = empty($arrListItem['purchase_order_status']) ? '' : intval($arrListItem['purchase_order_status']);
            $arrRoundResult['warehouse_name'] = empty($arrListItem['warehouse_name']) ? '' : strval($arrListItem['warehouse_name']);
            $arrRoundResult['purchase_order_plan_time'] = empty($arrListItem['purchase_order_plan_time']) ? '' : intval($arrListItem['purchase_order_plan_time']);
            $arrRoundResult['stockin_time'] = empty($arrListItem['stockin_time']) ? '' : intval($arrListItem['stockin_time']);
            $arrRoundResult['purchase_order_plan_amount'] = empty($arrListItem['purchase_order_plan_amount']) ? '' : intval($arrListItem['purchase_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? '' : intval($arrListItem['stockin_order_real_amount']);
            $arrRoundResult['purchase_order_remark'] = empty($arrListItem['purchase_order_remark']) ? '' : strval($arrListItem['purchase_order_remark']);

            $arrFormatResult[] = $arrRoundResult;
        }

        return [
            'total' => $arrRet['total'],
            'list' => $arrFormatResult,
        ];
    }
}
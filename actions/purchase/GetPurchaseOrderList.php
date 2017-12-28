<?php
/**
 * @name Action_GetPurchaseOrderList
 * @desc 获取采购订单列表（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetPurchaseOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'purchase_order_status' => 'str|default[10,20,30,31]',
        'warehouse_id' => 'str',
        'purchase_order_id' => 'regex|patern[/^(PUR\d{13})?$/]',
        'vendor_id' => 'int|min[0]',
        'create_time_start' => 'int|min[0]',
        'create_time_end' => 'int|min[0]',
        'purchase_order_plan_time_start' => 'int|min[0]',
        'purchase_order_plan_time_end' => 'int|min[0]',
        'stockin_time_start' => 'int|min[0]',
        'stockin_time_end' => 'int|min[0]',
        'page_num' => 'int|default[1]|min[1]',
        'page_size' => 'int|required|min[1]|max[100]',
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
        $this->objPage = new Service_Page_Purchase_GetPurchaseOrderList();
    }

    /**
     * format result, output data format process
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [];
        // 返回结果数据
        if (empty($arrRet['list'])) {
            return $arrFormatResult;
        }

        $arrRetList = $arrRet['list'];

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
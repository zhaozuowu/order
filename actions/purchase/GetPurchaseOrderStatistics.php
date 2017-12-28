<?php
/**
 * @name Action_GetPurchaseOrderStatistics
 * @desc 获取采购单状态统计
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
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        // 格式化数据结果
        $arrFormatResult = [];
        // 返回结果数据
        if (empty($arrRet)) {
            return $arrFormatResult;
        }

        $arrRetList = $arrRet;
        $intTotal = 0;
        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['purchase_order_status'] = empty($arrListItem['purchase_order_status']) ? '' : intval($arrListItem['purchase_order_status']);
            $arrRoundResult['purchase_order_status_count'] = empty($arrListItem['purchase_order_status_count']) ? 0 : intval($arrListItem['purchase_order_status_count']);
            $intTotal += intval($arrRoundResult['purchase_order_status_count']);
            $arrFormatResult[] = $arrRoundResult;
        }

        // 计算总数统计
        $arrRoundResult = [];
        $arrRoundResult['purchase_order_status'] = 0;
        $arrRoundResult['purchase_order_status_count'] = $intTotal;
        $arrFormatResult[] = $arrRoundResult;

        return [
            'list' => $arrFormatResult,
        ];
    }
}
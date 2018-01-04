<?php
/**
 * @name Action_GetStockinOrderDetail
 * @desc 查询入库单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_id' => 'regex|patern[/^SIO\d{13}$/]',
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
        $this->objPage = new Service_Page_Stockin_GetStockinOrderDetail();
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
            $strSourceOrderId = '';
            // 不同的入库单类型对应的前缀
            $intStockInType = intval($arrRet['stockin_order_type']);
            if(!empty($intStockInType)) {
                if(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockInType){
                    $strSourceOrderId = empty($arrRet['source_order_id']) ? '' : Nscm_Define_OrderPrefix::ASN . intval($arrRet['source_order_id']);
                }else if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RETURN == $intStockInType){
                    $strSourceOrderId = empty($arrRet['source_order_id']) ? '' : Nscm_Define_OrderPrefix::SOO . intval($arrRet['source_order_id']);
                }
            }
            $arrRoundResult['source_order_id'] = $strSourceOrderId;
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? '' : Nscm_Define_OrderPrefix::SIO . intval($arrRet['stockin_order_id']);
            $arrRoundResult['warehouse_name'] = empty($arrRet['warehouse_name']) ? '' : strval($arrRet['warehouse_name']);
            $arrRoundResult['reserve_order_plan_time'] = empty($arrRet['reserve_order_plan_time']) ? '' : intval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['stockin_order_total_price'] = empty($arrRet['stockin_order_total_price']) ? '' : intval($arrRet['stockin_order_total_price']);
            $arrRoundResult['stockin_order_total_price_tax'] = empty($arrRet['stockin_order_total_price_tax']) ? '' : intval($arrRet['stockin_order_total_price_tax']);
            $arrRoundResult['stockin_order_plan_amount'] = empty($arrRet['stockin_order_plan_amount']) ? '' : intval($arrRet['stockin_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrRet['stockin_order_real_amount']) ? '' : intval($arrRet['stockin_order_real_amount']);
            $arrRoundResult['stockin_order_remark'] = empty($arrRet['stockin_order_remark']) ? '' : strval($arrRet['stockin_order_remark']);
            $arrFormatResult = $arrRoundResult;
        }

        return $arrFormatResult;
    }
}
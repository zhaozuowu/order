<?php
/**
 * @name Action_GetStockinStockoutOrderInfoApi
 * @desc 查询销退入库单（包括商品列表）详情API
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinStockoutOrderInfoApi extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_id' => 'regex|patern[/^SIO\d{13}$/]',
    ];

    /**
     * hide price sentences on output info
     * @var bool
     */
    protected $boolHidePrice = true;

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
        $this->objPage = new Service_Page_Stockin_GetStockinStockoutOrderInfo();
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
        if (!empty($arrRet)) {
            $arrRoundResult = [];
            $strSourceOrderId = '';
            // 不同的入库单类型对应的前缀
            $intStockInType = intval($arrRet['stockin_order_type']);
            // 不显示非销退入库类型的
            if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT != $intStockInType) {
                return $arrFormatResult;
            }
            if (!empty($intStockInType)) {
                if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockInType) {
                    $strSourceOrderId = empty($arrRet['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::ASN . strval($arrRet['source_order_id']);
                } else if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intStockInType) {
                    $strSourceOrderId = empty($arrRet['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::SOO . strval($arrRet['source_order_id']);
                }
            }
            $arrRoundResult['source_order_id'] = $strSourceOrderId;
            $arrRoundResult['create_time_text'] = Order_Util::getFormatDateTime($arrRet['create_time']);
            $arrRoundResult['stockin_reason_text'] = empty($arrRet['stockin_order_reason_text'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : $arrRet['stockin_order_reason_text'];
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrRet['stockin_order_id']);
            $arrRoundResult['warehouse_name'] = empty($arrRet['warehouse_name']) ? ''
                : strval($arrRet['warehouse_name']);
            $arrRoundResult['warehouse_address'] = empty($arrRet['warehouse_address']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['warehouse_address']);
            $arrRoundResult['stockin_order_status_text'] = empty($arrRet['stockin_order_status']) ? ''
                : Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_MAP[
                    intval($arrRet['stockin_order_status'])
                ];
            $arrRoundResult['reserve_order_plan_time'] =
                empty($arrRet['reserve_order_plan_time']) ? 0
                    : intval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($arrRet['reserve_order_plan_time']);
            $arrRoundResult['stockin_order_plan_amount'] = empty($arrRet['stockin_order_plan_amount']) ? 0
                : intval($arrRet['stockin_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrRet['stockin_order_real_amount']) ? 0
                : intval($arrRet['stockin_order_real_amount']);
            $arrRoundResult['stockin_order_remark'] = empty($arrRet['stockin_order_remark']) ? ''
                : strval($arrRet['stockin_order_remark']);
            $arrRoundSkuList = $arrRet['skus_list_info']['list'];
            foreach ($arrRoundSkuList as $skuItems){
                $arrSkuInfo = [];
                $arrSkuInfo['sku_id'] = $skuItems['sku_id'];
                $arrSkuInfo['sku_name'] = $skuItems['sku_name'];
                $arrSkuInfo['stockin_reason_text'] =
                    isset(Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_DEFINE[intval($skuItems['stockin_reason'])])
                    ? Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[intval($skuItems['stockin_reason'])]
                    : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
                $arrSkuInfo['reserve_order_sku_plan_amount'] = $skuItems['reserve_order_sku_plan_amount'];
                $arrSkuInfo['stockin_order_sku_real_amount'] = $skuItems['stockin_order_sku_real_amount'];

                $arrRoundResult['sku_list'][] = $arrSkuInfo;
            }
            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult = $arrRoundResult;
        }
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }
}
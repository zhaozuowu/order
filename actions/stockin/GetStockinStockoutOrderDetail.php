<?php
/**
 * @name Action_GetStockinStockoutOrderDetail
 * @desc 查询销退入库单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinStockoutOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_id' => 'regex|patern[/^SIO\d{13}$/]',
    ];

    /**
     * filter price fields
     * @var array
     */
    protected $arrPriceFields = [
        'stockin_order_total_price_yuan',
        'stockin_order_total_price_tax_yuan',
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
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrRet['stockin_order_id']);
            $arrRoundResult['warehouse_name'] = empty($arrRet['warehouse_name']) ? ''
                : strval($arrRet['warehouse_name']);
            $arrRoundResult['city_id'] = empty($arrRet['city_id']) ? 0
                : intval($arrRet['city_id']);
            $arrRoundResult['city_name'] = empty($arrRet['city_name']) ? ''
                : strval($arrRet['city_name']);
            $arrRoundResult['reserve_order_plan_time'] =
                empty($arrRet['reserve_order_plan_time']) ? 0
                    : intval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($arrRet['reserve_order_plan_time']);
            $arrRoundResult['stockin_order_total_price_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrRet['stockin_order_total_price']);
            $arrRoundResult['stockin_order_total_price_tax_yuan'] =
                Nscm_Service_Price::convertDefaultToYuan($arrRet['stockin_order_total_price_tax']);
            $arrRoundResult['stockin_order_plan_amount'] = empty($arrRet['stockin_order_plan_amount']) ? 0
                : intval($arrRet['stockin_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrRet['stockin_order_real_amount']) ? 0
                : intval($arrRet['stockin_order_real_amount']);
            $arrRoundResult['source_supplier_id'] = empty($arrRet['source_supplier_id']) ? 0
                : intval($arrRet['source_supplier_id']);
            $arrRoundResult['stockin_order_creator_name'] =
                empty($arrRet['stockin_order_creator_name']) ? ''
                    : strval($arrRet['stockin_order_creator_name']);
            $arrRoundResult['source_info'] = empty($arrRet['source_info']) ? ''
                : strval($arrRet['source_info']);
            $arrRoundResult['stockin_order_remark'] = $this->formatStockInOrderRemark($arrRet);

            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult = $arrRoundResult;
        }
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }

    private function formatStockInOrderRemark($arrRet)
    {
        $stockInOrderRemark  =  empty($arrRet['stockin_order_remark']) ? '' : strval($arrRet['stockin_order_remark']);
        $assetInformation = empty($arrRet['asset_information']) ? []:json_decode($arrRet['asset_information'],true);
        if(!empty($assetInformation)) {
            foreach ($assetInformation as $item) {
                $stockInOrderRemark.=":".$item['device_no'].":".Order_Define_BusinessFormOrder::ORDER_DEVICE_MAP[$item['device_type']];

            }
        }
        return $stockInOrderRemark;

    }
}
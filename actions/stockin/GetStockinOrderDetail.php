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
        'stockin_order_id' => 'regex|patern[/^((SIO\d{13})|(\d{15}))$/]',
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
     * @throws Nscm_Exception_System
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
            $arrRoundResult['warehouse_session_privilege'] =
                boolval(!Nscm_Service_Auth::checkWarehouse([$arrRet['warehouse_id']]));
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
            $arrRoundResult['stockin_order_remark'] = empty($arrRet['stockin_order_remark']) ? ''
                : strval($arrRet['stockin_order_remark']);
            $arrRoundResult['stockin_order_status'] = intval($arrRet['stockin_order_status']);
            $arrRoundResult['display_operate_tip'] = empty($arrRet['display_operate_tip']) ? false
                : boolval($arrRet['display_operate_tip']);
            $arrRoundResult['last_operate_time'] = intval($arrRet['last_operate_time']);
            $arrRoundResult['last_operate_name'] = empty($arrRet['last_operate_name'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['last_operate_name']);
            $arrRoundResult['last_operate_device'] = empty($arrRet['last_operate_device'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['last_operate_device']);
            $arrRoundResult['shipment_order_id'] = intval($arrRet['shipment_order_id']);
            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult = $arrRoundResult;
        }
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }
}
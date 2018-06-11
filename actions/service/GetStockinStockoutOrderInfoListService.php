<?php
/**
 * @name Action_GetStockinStockoutOrderInfoListApi
 * @desc 查询销退入库单（包括商品列表）详情列表（多单查询）API
 * @author chenwende@iwaimai.baidu.com
 */

class Action_Service_GetStockinStockoutOrderInfoListService extends Order_Base_ServiceAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_ids' => 'regex|patern[/^(((SIO\d{13}),)?)+(SIO\d{13})$/]',
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
        $this->objPage = new Service_Page_Stockin_GetStockinStockoutOrderInfoList();
    }

    /**
     * format result, output data format process
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [];
        if (empty($arrRet)) {
            return $arrFormatResult;
        }

        // 格式化数据结果
        foreach ($arrRet as $orderInfo) {
            $arrRoundResult = [];
            $strSourceOrderId = '';
            // 不同的入库单类型对应的前缀
            $intStockInType = intval($orderInfo['stockin_order_type']);
            // 不显示非销退入库类型的
            if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT != $intStockInType) {
                continue;
            }
            if (!empty($intStockInType)) {
                if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockInType) {
                    $strSourceOrderId = empty($orderInfo['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::ASN . strval($orderInfo['source_order_id']);
                } else if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intStockInType) {
                    $strSourceOrderId = empty($orderInfo['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::SOO . strval($orderInfo['source_order_id']);
                }
            }
            $arrRoundResult['source_order_id'] = $strSourceOrderId;
            $arrRoundResult['create_time_text'] = Order_Util::getFormatDateTime($orderInfo['create_time']);
            $arrRoundResult['stockin_reason_text'] = empty($orderInfo['stockin_order_reason_text'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : $orderInfo['stockin_order_reason_text'];
            $arrRoundResult['stockin_order_id'] = empty($orderInfo['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($orderInfo['stockin_order_id']);
            $arrRoundResult['warehouse_name'] = empty($orderInfo['warehouse_name']) ? ''
                : strval($orderInfo['warehouse_name']);
            $arrRoundResult['warehouse_address'] = empty($orderInfo['warehouse_address']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($orderInfo['warehouse_address']);
            $arrRoundResult['stockin_order_status_text'] = empty($orderInfo['stockin_order_status']) ? ''
                : Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_MAP[
                intval($orderInfo['stockin_order_status'])
                ];
            $arrRoundResult['reserve_order_plan_time'] =
                empty($orderInfo['reserve_order_plan_time']) ? 0
                    : intval($orderInfo['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($orderInfo['reserve_order_plan_time']);
            $arrRoundResult['stockin_order_plan_amount'] = empty($orderInfo['stockin_order_plan_amount']) ? 0
                : intval($orderInfo['stockin_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($orderInfo['stockin_order_real_amount']) ? 0
                : intval($orderInfo['stockin_order_real_amount']);
            $arrRoundResult['stockin_order_remark'] = empty($orderInfo['stockin_order_remark']) ? ''
                : strval($orderInfo['stockin_order_remark']);

            // 添加资产信息
            $arrRoundResult['asset_information'] = $this->formatAssetInformation($orderInfo['asset_information']);

            $arrOrderSkuList = $orderInfo['skus_list_info'];
            $arrRoundSkuList = [];
            foreach ($arrOrderSkuList as $skuItems){
                $arrSkuInfo = [];
                $arrSkuInfo['sku_id'] = $skuItems['sku_id'];
                $arrSkuInfo['upc_unit_num'] = empty($skuItems['upc_unit_num'])
                    ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : '1*' . intval($skuItems['upc_unit_num']);
                $arrSkuInfo['upc_unit_text'] =
                    isset(Nscm_Define_Sku::UPC_UNIT_MAP[intval($skuItems['upc_unit'])])
                        ? Nscm_Define_Sku::UPC_UNIT_MAP[intval($skuItems['upc_unit'])]
                        : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
                $arrSkuInfo['sku_name'] = $skuItems['sku_name'];
                $arrSkuInfo['stockin_reason_text'] =
                    isset(Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_DEFINE[intval($skuItems['stockin_reason'])])
                        ? Order_Define_StockinOrder::STOCKIN_STOCKOUT_REASON_MAP[intval($skuItems['stockin_reason'])]
                        : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
                $arrSkuInfo['reserve_order_sku_plan_amount'] = $skuItems['reserve_order_sku_plan_amount'];
                $arrSkuInfo['stockin_order_sku_real_amount'] = $skuItems['stockin_order_sku_real_amount'];
                $arrRoundSkuList[] = $arrSkuInfo;
            }
            $arrRoundResult['sku_list'] = $arrRoundSkuList;
            $arrRoundResult = $this->filterPrice($arrRoundResult);
            $arrFormatResult[] = $arrRoundResult;
        }
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }

    /**
     * 格式化处理设备资产信息
     * @param $strAssetInfo
     * @return array
     */
    private function formatAssetInformation($strAssetInfo)
    {
        $arrResult = [];
        if (empty($strAssetInfo)) {
            return $arrResult;
        }

        $arrAssetInfo = json_decode($strAssetInfo, true);
        if (empty($arrAssetInfo)) {
            return $arrResult;
        }

        foreach ($arrAssetInfo as $info) {
            $infoLine = [
                'device_no' => strval($info['device_no']),
                'device_type' => intval($info['device_type']),
                'device_type_text' => (
                    isset(Order_Define_BusinessFormOrder::ORDER_DEVICE_MAP[intval($info['device_type'])])
                        ? Order_Define_BusinessFormOrder::ORDER_DEVICE_MAP[intval($info['device_type'])]
                        : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                ),
            ];
            $arrResult[] = $infoLine;
        }

        return $arrResult;
    }
}


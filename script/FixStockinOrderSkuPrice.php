#!php/bin/php
<?php
/**
 * @name FixStockinOrderSkuPrice
 * @desc 修复入库单价格
 * @author hang.song02@ele.me
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new FixStockinOrderSkuPrice();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class FixStockinOrderSkuPrice
{

    /**
     * limit
     */
    const LIMIT = 20;

    /**
     * 仓库sku关系
     * @var array
     */
    private $arrNeedFixWarehouseSkuMap = [];

    /**
     * @var Dao_Redis_Common
     */
    private $objRedis;
    /**
     * @var Dao_Redis_StatisticsDemotion
     */
    private $daoRedis;
    /**
     * @var Dao_Ral_Stock
     */
    private $daoStock;

    /**
     * @var Dao_Ral_Sku
     */
    private $daoSku;

    private $limit = 100;

    /**
     * work
     * @throws Nscm_Exception_Error
     */
    public function work()
    {
        $this->objRedis = new Dao_Redis_Common();
        $this->daoStock = new Dao_Ral_Stock();
        $this->daoSku = new Dao_Ral_Sku();
        $this->daoRedis = new Dao_Redis_StatisticsDemotion();
        $this->getNeedFixWarehouseSku();
        $startTime = time();
        $this->fixStockinOrder();
        $endTime = time();
        echo "入库单处理成功,处理时长[".$endTime - $startTime."]";
        $this->fixStockOutOrder();
        $endOutTime = time();
        echo "出库单处理成功,处理时长[".$endOutTime - $endTime."]";
    }

    private function getNeedFixWarehouseSku()
    {
        $this->arrNeedFixWarehouseSkuMap = $this->objRedis->getNeedFixWarehouseSkuList();
    }

    private function setNeedFixWarehouseSku()
    {
        $this->objRedis->setNeedFixWarehouseSkuList($this->arrNeedFixWarehouseSkuMap);
    }

    private function fixStockinOrder()
    {
        Bd_Log::trace("STOCK_IN_ORDER START".time());
        foreach ($this->arrNeedFixWarehouseSkuMap as $intWarehouseId => $arrWarehouseSkusInfo) {
            $intOffset = 0;
            $intStatus = $arrWarehouseSkusInfo['status'];
            $arrConds = [
                'is_delete' => Nscm_Define_Const::ENABLE,
                'warehouse_id' => $intWarehouseId,
                'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM,
                'create_time' => ['>' , strtotime('2018-04-01')],
            ];
            if (1 == $intStatus) {
                $startTime = time();
                do {
                    $arrStockOrderInfo = Model_Orm_StockinOrder::findRows(['stockin_order_id'], $arrConds, ['id' => 'asc'], $intOffset, $this->limit);
                   /* if (0 == count($arrStockOrderInfo)) {
                        continue;
                    }*/
                    $arrStockOrderIds = array_column($arrStockOrderInfo, 'stockin_order_id');
                    $intOrderType = Nscm_Define_Stock::STOCK_IN_TYPE_SALE_RETURN;
                    //通过单号获取价格
                    $arrStockOrdersSkuPrice = $this->daoStock->getBatchSkuPrice($arrStockOrderIds, $intOrderType);
                    //diff
                    $arrStockOrdersSkuPriceSkuIds = array_column($arrStockOrdersSkuPrice, 'order_id');
                    $arrSkuIdsDiff = array_diff($arrStockOrderIds, $arrStockOrdersSkuPriceSkuIds);
                    echo "[DIFF]STOCK_IN_ORDER_IDS:".implode(',', $arrSkuIdsDiff) . PHP_EOL;
//                $arrStockOrdersSkuPrice = [
//                        [
//                                "order_id" => $arrStockOrderIds[0],
//                                "sku_list" => [
//                                        "sku_id" => 1000025,
//                                        "sku_price" => 123123,
//                                        "sku_price_tax" => 123234,
//                                ],
//                        ]
//                ];
                    Bd_Log::trace("STOCK_IN_ORDER_OFFSET". $intOffset);
                    foreach ($arrStockOrdersSkuPrice as $arrStockOrderSkuPrice) {
                        try {
                            Model_Orm_StockinOrder::getConnection()->transaction(function () use ($arrStockOrderSkuPrice) {
                                $stockInOrderId = $arrStockOrderSkuPrice['order_id'];
                                $intStockInOrderSkuPriceAmount = 0;
                                $intStockInOrderSkuPriceTaxAmount = 0;
                                foreach ($arrStockOrderSkuPrice['sku_list'] as $arrSkuList) {
                                    $intSkuPriceAmount = 0;
                                    $intSkuPriceTaxAmount = 0;
                                    $arrSkuConditons = [
                                        'stockin_order_id' => $stockInOrderId,
                                        'sku_id' => $arrSkuList['sku_id'],
                                    ];
                                    $arrUpdateInfo = [
                                        'sku_price' => $arrSkuList['sku_price'],
                                        'sku_price_tax' => $arrSkuList['sku_price_tax'],
                                    ];
                                    $objSkuInfo = Model_Orm_StockinOrderSku::findOne($arrSkuConditons);
                                    if (!empty($objSkuInfo)) {
                                        $intStockInOrderSkuPriceAmount += $objSkuInfo->stockin_order_sku_real_amount * $arrSkuList['sku_price'];
                                        $intStockInOrderSkuPriceTaxAmount += $objSkuInfo->stockin_order_sku_real_amount * $arrSkuList['sku_price_tax'];
                                        $intSkuPriceAmount += $objSkuInfo->stockin_order_sku_real_amount * $arrSkuList['sku_price'];
                                        $intSkuPriceTaxAmount += $objSkuInfo->stockin_order_sku_real_amount * $arrSkuList['sku_price_tax'];
                                        $arrUpdateInfo['stockin_order_sku_total_price'] = $intSkuPriceAmount;
                                        $arrUpdateInfo['stockin_order_sku_total_price_tax'] = $intSkuPriceTaxAmount;
                                        $objSkuInfo->update($arrUpdateInfo);
                                    } else {
                                        Bd_Log::trace(sprintf("sku not existed order_id[%d],sku_id[%d]", $stockInOrderId, $arrSkuList['sku_id']));
                                    }
                                    $arrOrderConds = [
                                        'stockin_order_id' => $stockInOrderId,
                                        'is_delete' => Nscm_Define_Const::ENABLE,
                                    ];
                                    //更新实际总价
                                    $objOrderInfo = Model_Orm_StockinOrder::findOne($arrOrderConds);
                                    if (!empty($objOrderInfo)) {
                                        $objOrderInfo->update([
                                            'stockin_order_total_price' => $intStockInOrderSkuPriceAmount,
                                            'stockin_order_total_price_tax' => $intStockInOrderSkuPriceTaxAmount,
                                        ]);
                                    }
                                }
                            });
                            //通知报表
                            $this->daoRedis->addStatisticsOrder($arrStockOrderSkuPrice['order_id'], Order_Statistics_Type::ACTION_UPDATE, Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT);
                            echo "[SUCCESS]STOCK_IN_ORDER_ID:". $arrStockOrderSkuPrice['order_id'] .PHP_EOL;
                        } catch (Exception $e) {
                            echo "[FAILED]STOCK_IN_ORDER_ID:". $arrStockOrderSkuPrice['order_id'] .PHP_EOL;

                            Bd_Log::trace(sprintf("update failed order_info[%s]", json_encode($arrStockOrderSkuPrice)));
                        }
                        $intOffset += $this->limit;
                    }
                    $intOffset += $this->limit;
                } while ($this->limit == count($arrStockOrderInfo));
                $this->arrNeedFixWarehouseSkuMap[$intWarehouseId]['status'] = 2;
                $endTime = time();
                echo "入库单仓库处理成功[$intWarehouseId],处理时长[". $endTime - $startTime."]";
            }
            //设置warehouse已处理
            $this->setNeedFixWarehouseSku();
        }
        Bd_Log::trace("STOCK_IN_ORDER END".time());

    }

    private function fixStockOutOrder()
    {
        Bd_Log::trace("STOCK_OUT_ORDER START".time());
        foreach ($this->arrNeedFixWarehouseSkuMap as $intWarehouseId => $arrWarehouseSkusInfo) {
            $intOffset = 0;
            $intStatus = $arrWarehouseSkusInfo['status'];
            $arrSkuIds = $arrWarehouseSkusInfo['sku_id_list'];

            if (2 == $intStatus) {
                $startTime = time();
                //获取商品基础信息用于计算配送价
                $arrSkuBaseInfoMap = $this->daoSku->getSkuInfos($arrSkuIds);

                $arrConds = [
                    'is_delete' => Nscm_Define_Const::ENABLE,
                    'warehouse_id' => $intWarehouseId,
                    'create_time' => ['>', strtotime('2018-04-01')],
                ];
                do {
                    $arrStockOrderInfo = Model_Orm_StockoutOrder::findRows(['stockout_order_id'], $arrConds, ['id' => 'asc'], $intOffset, $this->limit);
                    if (0 == count($arrStockOrderInfo)) {
                        continue;
                    }
                    $arrStockOrderIds = array_column($arrStockOrderInfo, 'stockout_order_id');
                    $intOrderType = Nscm_Define_Stock::STOCK_OUT_TYPE_SALE;
                    //通过单号获取价格
                    $arrStockOrdersSkuPrice = $this->daoStock->getBatchSkuPrice($arrStockOrderIds, $intOrderType);
//                $arrStockOrdersSkuPrice = [
//                    [
//                        "order_id" => $arrStockOrderIds[0],
//                        "sku_list" => [
//                                [
//                                    "sku_id" => 1000025,
//                                    "sku_price" => 123123,
//                                    "sku_price_tax" => 123234,
//                                ]
//                            ],
//                    ]
//                ];
                    Bd_Log::trace("STOCK_OUT_ORDER_OFFSET". $intOffset);
                    //diff
                    $arrStockOrdersSkuPriceSkuIds = array_column($arrStockOrdersSkuPrice, 'order_id');
                    $arrSkuIdsDiff = array_diff($arrStockOrderIds, $arrStockOrdersSkuPriceSkuIds);
                    echo "[DIFF]STOCK_OUT_ORDER_IDS:".implode(',', $arrSkuIdsDiff) . PHP_EOL;
                    foreach ($arrStockOrdersSkuPrice as $arrStockOrderSkuPrice) {
                        try {
                            Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrStockOrderSkuPrice, $arrSkuBaseInfoMap) {
                                $stockOutOrderId = $arrStockOrderSkuPrice['order_id'];
                                $intStockInOrderSkuPriceAmount = 0;
                                $intStockInOrderSkuPriceTaxAmount = 0;
                                $arrOrderConds = [
                                    'stockout_order_id' => $stockOutOrderId,
                                    'is_delete' => Nscm_Define_Const::ENABLE,
                                ];
                                $objOrderInfo = Model_Orm_StockoutOrder::findOne($arrOrderConds);
                                if (empty($objOrderInfo)) {
                                    Bd_Log::trace(sprintf("stock out order not existed,order_id[%d]", $stockOutOrderId));
                                }
                                $intBusinessOrderId = $objOrderInfo->business_form_order_id;
                                foreach ($arrStockOrderSkuPrice['sku_list'] as $arrSkuList) {
                                    $intSkuPriceAmount = 0;
                                    $intSkuPriceTaxAmount = 0;
                                    $arrSkuConditions = [
                                        'stockout_order_id' => $stockOutOrderId,
                                        'sku_id' => $arrSkuList['sku_id'],
                                    ];
                                    $arrUpdateInfo = [
                                        'cost_price' => $arrSkuList['sku_price'],
                                        'cost_price_tax' => $arrSkuList['sku_price_tax'],
                                    ];

                                    $arrBusinessOrderConditions = [
                                        'business_form_order_id' => $intBusinessOrderId,
                                        'is_delete' => Nscm_Define_Const::ENABLE,
                                    ];
                                    $objBusinessOrderInfo = Model_Orm_BusinessFormOrder::findOne($arrBusinessOrderConditions);
                                    $arrBusinessSkuConditions = [
                                        'business_form_order_id' => $intBusinessOrderId,
                                        'sku_id' => $arrSkuList['sku_id'],
                                    ];
                                    $objBusinessOrderSkuInfo = Model_Orm_BusinessFormOrderSku::findOne($arrBusinessSkuConditions);
                                    //计算配送价
                                    $arrSendPriceInfo = $this->getSendPriceInfo($arrSkuBaseInfoMap[$arrSkuList['sku_id']]['sku_business_form_detail'], $objBusinessOrderInfo->business_form_order_type);
                                    if (Order_Define_Sku::SKU_PRICE_TYPE_BENEFIT
                                        == $arrSendPriceInfo['sku_price_type']) {
                                        $arrUpdateInfo['send_price'] = $arrSkuList['sku_price']
                                            * (1 + $arrSendPriceInfo['sku_price_value']/100);
                                        $arrUpdateInfo['send_price_tax'] = $arrSkuList['sku_price_tax']
                                            * (1 + $arrSendPriceInfo['sku_price_value']/100.0);
                                    }
                                    if (Order_Define_Sku::SKU_PRICE_TYPE_COST
                                        == $arrSendPriceInfo['sku_price_type']) {
                                        $arrUpdateInfo['send_price'] = $arrSkuList['sku_price'];
                                        $arrUpdateInfo['send_price_tax'] = $arrSkuList['sku_price_tax'];
                                    }
                                    if (Order_Define_Sku::SKU_PRICE_TYPE_STABLE
                                        == $arrSendPriceInfo['sku_price_type']) {
                                        $arrUpdateInfo['send_price_tax'] = intval($arrSendPriceInfo['sku_price_value']);
                                        $intTaxRate = $arrSkuBaseInfoMap[$arrSkuList['sku_id']]['sku_tax_rate'];
                                        $arrUpdateInfo['send_price'] = intval($arrSendPriceInfo['sku_price_value'])
                                            / (1 + Order_Define_Sku::SKU_TAX_NUM[$intTaxRate] / 100.0);
                                    }

                                    $objSkuInfo = Model_Orm_StockoutOrderSku::findOne($arrSkuConditions);
                                    if (!empty($objSkuInfo)) {
                                        $intStockInOrderSkuPriceAmount += $objSkuInfo->distribute_amount * $arrSkuList['sku_price'];
                                        $intStockInOrderSkuPriceTaxAmount += $objSkuInfo->distribute_amount * $arrSkuList['sku_price_tax'];
                                        $intSkuPriceAmount += $objSkuInfo->distribute_amount * $arrSkuList['sku_price'];
                                        $intSkuPriceTaxAmount += $objSkuInfo->distribute_amount * $arrSkuList['sku_price_tax'];
                                        $arrUpdateInfo['cost_total_price'] = $intSkuPriceAmount;
                                        $arrUpdateInfo['cost_total_price_tax'] = $intSkuPriceTaxAmount;
                                        $arrUpdateInfo['send_total_price'] = $objSkuInfo->distribute_amount * $arrUpdateInfo['send_price'];
                                        $arrUpdateInfo['send_total_price_tax'] = $objSkuInfo->distribute_amount * $arrUpdateInfo['send_price_tax'];
                                        $objSkuInfo->update($arrUpdateInfo);
                                    } else {
                                        Bd_Log::trace(sprintf("stockout ourder sku not existed order_id[%d],sku_id[%d]", $stockOutOrderId, $arrSkuList['sku_id']));
                                    }

                                    //更新业态订单sku价格

                                    if (!empty($objBusinessOrderSkuInfo)) {
                                        unset($arrUpdateInfo['send_price_tax']);
                                        unset($arrUpdateInfo['cost_price_tax']);
                                        unset($arrUpdateInfo['send_total_price_tax']);
                                        unset($arrUpdateInfo['cost_total_price_tax']);
                                        $objBusinessOrderSkuInfo->update($arrUpdateInfo);
                                    } else {
                                        Bd_Log::trace(sprintf("business order sku not existed order_id[%d],sku_id[%d]", $stockOutOrderId, $arrSkuList['sku_id']));
                                    }
                                }
                                //更新实际总价
                                if (!empty($objOrderInfo)) {
                                    $objOrderInfo->update([
                                        'stockout_order_total_price' => $intStockInOrderSkuPriceAmount,
                                    ]);
                                }
                            });
                            //通知报表
                            $this->daoRedis->addStatisticsOrder($arrStockOrderSkuPrice['order_id'], Order_Statistics_Type::ACTION_UPDATE, Order_Statistics_Type::TABLE_STOCKOUT_ORDER);
                            echo "[SUCCESS]STOCK_OUT_ORDER_ID:". $arrStockOrderSkuPrice['order_id'] .PHP_EOL;
                        } catch (Exception $e) {
                            Bd_Log::trace(sprintf("update failed order_info[%s]", json_encode($arrStockOrderSkuPrice)));
                            echo "[FAILED]STOCK_OUT_ORDER_ID:". $arrStockOrderSkuPrice['order_id'] .PHP_EOL;
                        }
                    }
                    $intOffset += $this->limit;
                } while ($this->limit == count($arrStockOrderInfo));
                $this->arrNeedFixWarehouseSkuMap[$intWarehouseId]['status'] = 3;
                $endTime = time();
                echo "出库单仓库处理成功[$intWarehouseId],处理时长[". $endTime - $startTime."]";
            }
            //设置warehouse已处理
            $this->setNeedFixWarehouseSku();
        }
        Bd_Log::trace("STOCK_OUT_ORDER END".time());
    }

    /**
     * @param array $arrBusinessFormDetail
     * @param integer $intOrderType
     * @return array|mixed
     */
    protected function getSendPriceInfo($arrBusinessFormDetail, $intOrderType) {
        if (empty($arrBusinessFormDetail)) {
            return [];
        }
        $arrSendPriceInfo = [];
        foreach ((array)$arrBusinessFormDetail as $arrBusinessFormItem) {
            if ($intOrderType != $arrBusinessFormItem['type']) {
                continue;
            }
            $arrSendPriceInfo = $arrBusinessFormItem;
        }
        return $arrSendPriceInfo;
    }

}
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
     * work
     * @throws Nscm_Exception_Error
     */
    public function work()
    {
        $intNow = time();
        $this->getNeedFixWarehouseSku();
        $this->workTable();
    }

    private function getNeedFixWarehouseSku()
    {
        $objRedis = new Dao_Redis_Common();
        $this->arrNeedFixWarehouseSkuMap = $objRedis->getNeedFixWarehouseSkuList();
    }

    private function workTable()
    {
        $limit = 100;
        $intOffset = 0;
        foreach ($this->arrNeedFixWarehouseSkuMap as $intWarehouseId => $arrWarehouseSkusInfo) {
            $intStatus = $arrWarehouseSkusInfo['status'];
            $arrConds = [
                'is_delete' => Nscm_Define_Const::ENABLE,
                'warehouse_id' => $intWarehouseId,
                'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM,
            ];
            if (1 == $intStatus) {
                $arrStockOrderInfo = Model_Orm_StockinOrder::findRows('stockin_order_id', $arrConds, ['id' => 'asc'], $intOffset, $limit);
                if (0 == count($arrStockOrderInfo)) {
                    continue;
                }
                $arrStockOrderIds = array_column($arrStockOrderInfo, 'stockin_order_id');
                $intOrderType = Nscm_Define_Stock::STOCK_IN_TYPE_SALE_RETURN;
                //通过单号获取价格
//                $daoStock = new Dao_Ral_Stock();
//                $arrStockOrdersSkuPrice = $daoStock->getBatchSkuPrice($arrStockOrderIds, $intOrderType);
                $arrStockOrdersSkuPrice = [
                        [
                                "order_id" => $arrStockOrderIds[0],
                                "sku_list" => [
                                        "sku_id" => 1000025,
                                        "sku_price" => 123123,
                                        "sku_price_tax" => 123234,
                                ],
                        ]
                ];
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
                                    Bd_Log::trace("sku not existed order_id[%d],sku_id[%d]", $stockInOrderId, $arrSkuList['sku_id']);
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
                    } catch (Exception $e) {
                        Bd_Log::trace("update failed order_info[%s]", json_encode($arrStockOrderSkuPrice));
                    }
                }
            }
        }

    }

}
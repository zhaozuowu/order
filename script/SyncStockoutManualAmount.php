#!php/bin/php
<?php
/**
 * @name SyncStockoutManualAmount
 * @desc sync stockout manual amount
 * @author bochao.lv@ele.me
 */

Bd_Init::init();

try
{
    $objWork = new SyncStockoutManualAmount();
    $objWork->work();
} catch (Exception $e)
{
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class SyncStockoutManualAmount
{

    const LIMIT = 20;

    /**
     * work
     */
    public function work()
    {
        $arrSearchCondition = [
            'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT,
            'data_source' => 0,
        ];
        $intLimit = self::LIMIT;
        $arrOrder = [
            'id' => 'asc',
        ];
        $intOffset = 0;
        $arrUpdateOrderFields = [
            'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_MANUAL_CREATE,
        ];
        $arrUpdateOrderSkuFields = [
            'stockout_order_sku_amount' => new Wm_Orm_Expression('reserve_order_sku_plan_amount'),
        ];
        // query id
        $arrALLIds = Model_Orm_StockinOrder::findColumn('stockin_order_id', $arrSearchCondition, $arrOrder);
        do {
            $arrIds = array_slice($arrALLIds, $intOffset, $intLimit);
            $intCount = count($arrIds);
            if (empty($intCount)) {
                break;
            }
            $intOffset += $intCount;
            $arrUpdateCondition = [
                'stockin_order_id' => ['in', $arrIds],
            ];
            Model_Orm_StockinOrder::updateAll($arrUpdateOrderFields, $arrUpdateCondition);
            Model_Orm_StockinOrderSku::updateAll($arrUpdateOrderSkuFields, $arrUpdateCondition);
            Bd_Log::trace('modified id: ' . json_encode($arrIds));
            sleep(1);
        } while (!empty($intCount));
        Bd_Log::trace('/*********************** WORK SWIPE DATA_SOURCE & SKU_AMOUNT FINISH! ***********************/');
    }
}
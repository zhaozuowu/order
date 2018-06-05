<?php
/**
 * @name SetStockoutSkuPickupAmount.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/6/4 14:38
 */
Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new SetStockoutSkuPickupAmount();
    $intStockoutOrderId = $argv[1];
    $intSkuId = $argv[2];
    $intPickupAmount = $argv[3];
    if (empty($intPickupOrderId) || empty($intSkuId) || null === $intPickupAmount) {
        echo 'input params! stockout_order_id sku_id pickup_amount';
        exit;
    }
    $objAso->work(intval($intStockoutOrderId), intval($intSkuId), intval($intPickupAmount));
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class SetStockoutSkuPickupAmount
{
    /**
     * @param $intStockoutOrderId
     * @param $intSkuId
     * @param $intPickupAmount
     */
    public function work($intStockoutOrderId, $intSkuId, $intPickupAmount) {
        $arrCondition = [
            'stockout_order_id'=> $intStockoutOrderId,
            'sku_id' => $intSkuId,
        ];
        $ormStockoutSku = Model_Orm_StockoutOrderSku::findOne($arrCondition);
        Bd_Log::trace(sprintf('change_stockout_sku_pickup_amount, stockout_order_id[%d], sku_id[%d], old_amount[%d]' .
        'new_amount[%d]', $intStockoutOrderId, $intSkuId, $ormStockoutSku->pickup_amount, $intPickupAmount));
        $ormStockoutSku->pickup_amount = $intPickupAmount;
        $ormStockoutSku->update();
    }

}
<?php
/**
 * @name UpdateIllegalSkuPickupAmount.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/6/4 14:38
 */
Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new UpdateIllegalSkuPickupAmount();
    $intStockoutOrder = $argv[1];
    if (empty($intStockoutOrder)) {
        echo 'input stockout order id!';
        exit;
    }
    $objAso->work($intStockoutOrder);
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class UpdateIllegalSkuPickupAmount
{
    /**
     * @param $intStockoutOrder
     */
    public function work($intStockoutOrder) {
        $arrObjStockoutSkus = Model_Orm_StockoutOrderSku::findAll([
            'stockout_order_id' => $intStockoutOrder,
        ]);
        foreach ($arrObjStockoutSkus as $objStockoutSku) {
            $objStockoutSku->pickup_amount = 0;
            $objStockoutSku->update();
        }

    }

}
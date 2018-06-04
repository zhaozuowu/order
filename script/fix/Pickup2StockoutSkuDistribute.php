<?php
/**
 * @name Pickup2StockoutSkuDistribute.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/6/4 14:38
 */
Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new Pickup2StockoutSkuDistribute();
    $intPickupOrderId = $argv[1];
    if (empty($intPickupOrderId)) {
        echo 'input pickup order id!';
        exit;
    }
    $objAso->work($intPickupOrderId);
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class Pickup2StockoutSkuDistribute
{
    /**
     * @param $intPickupOrderId
     */
    public function work($intPickupOrderId) {
        $intPickupOrderId = intval($intPickupOrderId);
        $arrStockoutOrders = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($intPickupOrderId);
        $ormStockoutOrderSku = new Model_Orm_StockoutOrderSku();
        $arrStockoutOrderSkus = $ormStockoutOrderSku->getStockoutOrderSkusByOrderIds($arrStockoutOrders);
        $arrPickupOrderSkus = Model_Orm_PickupOrderSku::getSkuListByPickupOrderId($intPickupOrderId);
        // hash pickup order skus
        $arrHashPickupOrderSkus = array_combine(array_column($arrPickupOrderSkus, 'sku_id'), $arrPickupOrderSkus);
        foreach ($arrStockoutOrderSkus as $arrStockoutOrderSku) {
            // update sku amount
            $intSkuId = $arrStockoutOrderSku['sku_id'];
            if ($arrHashPickupOrderSkus[$intSkuId]['pickup_amount'] > $arrStockoutOrderSku['distribute_amount']) {
                $intPickupAmount = $arrStockoutOrderSku['distribute_amount'];
            } else {
                $intPickupAmount = $arrHashPickupOrderSkus[$intSkuId]['pickup_amount'] ?? 0;
            }
            $this->updateStockoutOrderSku($arrStockoutOrderSku, $intPickupAmount);
            $arrHashPickupOrderSkus[$intSkuId]['pickup_amount'] -= $intPickupAmount;
        }

    }

    public function updateStockoutOrderSku($arrStockoutOrderSku, $intPickupAmount) {
        if ($arrStockoutOrderSku['pickup_amount'] == $intPickupAmount) {
            return;
        }
        $ormStockoutSku = Model_Orm_StockoutOrderSku::populate($arrStockoutOrderSku);
        $ormStockoutSku->pickup_amount = $intPickupAmount;
        $ormStockoutSku->update();
    }

}
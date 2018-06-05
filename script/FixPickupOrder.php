#!php/bin/php
<?php
/**
 * @name FixPickupOrder
 * @desc 修复捡货单状态
 * @author hang.song02@ele.me
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $pickOrderId = getenv("pickupOrderId");
    $objAso = new FixPickupOrder();
    $objAso->work($pickOrderId);
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class FixPickupOrder
{

    /**
     * limit
     */
    const LIMIT = 20;

    public function work($pickOrderId)
    {

        $pickOrderId = empty($pickOrderId) ? '1806033909172':$pickOrderId;
        $objPickUpOrder =   new Service_Data_PickupOrder();
        $startTime = time();
        $objPickUpOrder->fixPickupOrder($pickOrderId);
        $endTime = time();
        echo sprintf("出库单处理成功,处理时长[%d]\n", $endTime - $startTime);
    }



}
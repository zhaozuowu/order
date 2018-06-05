<?php
/**
 * @name UpdateHistoryStockinOrderIsPlaced.php
 * @desc UpdateHistoryStockinOrderIsPlaced.php
 * @author yu.jin03@ele.me
 */

Bd_Init::init();
class UpdateHistoryStockinOrderIsPlaced
{
    public function execute() {
        Model_Orm_StockinOrder::updateAll(['is_placed_order' => Order_Define_StockinOrder::STOCKIN_AUTO_PLACED],[]);
    }
}

$obj = new UpdateHistoryStockinOrderIsPlaced();
$obj->execute();
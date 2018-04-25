#!php/bin/php
<?php
/**
 * @name WriteStockinStockoutClientId
 * @desc swipe data
 * @author bochao.lv@ele.me
 */

Bd_Init::init();

try
{
    $objWork = new DeleteInvalidStockinOrder();
    $objWork->work();
} catch (Exception $e)
{
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class DeleteInvalidStockinOrder
{

    /**
     * work
     */
    public function work()
    {
        $conn = Bd_Db_ConnMgr::getConn('nwms_order/nwms_order_gzns');
        $sql = "select stockin_order_id from stockin_order where stockin_order_id not in (select DISTINCT stockin_order_id FROM stockin_order_sku) AND stockin_order_type = ".Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT ." AND is_delete = ".Order_Define_Const::NOT_DELETE;
        $arrStockInOrderList = $conn->query($sql);
        $arrStockInOrderIds = array_column($arrStockInOrderList, 'stockin_order_id');
        $arrCondition = ['stockin_order_id' => ['in', $arrStockInOrderIds]];
        $arrOrder = [
            'id' => 'asc',
        ];
        $objStockinOrders = Model_Orm_StockinOrder::findAll($arrCondition, $arrOrder);
        foreach ($objStockinOrders as $objStockinOrder) {
            $intStockinOrderId = $objStockinOrder->stockin_order_id;
            $objStockinOrder->is_delete = Order_Define_Const::IS_DELETE;
            $objStockinOrder->update();
            Bd_Log::trace('delete stock in order update.stockin order id: ' . $intStockinOrderId);
        }
        Bd_Log::trace('/******************************WORK SWIPE SUPPLIER FINISH!******************************/');
    }
}
#!php/bin/php
<?php
/**
 * @name SetNeedFixWarehouseSku
 * @desc 设置数量
 * @author hang.song02@ele.me
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new SetNeedFixWarehouseSku();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class SetNeedFixWarehouseSku
{
    /**
     * work
     * @throws Nscm_Exception_Error
     */
    public function work()
    {
        $this->setNeedFixWarehouse();
    }

    private function setNeedFixWarehouse()
    {
        $arrWarehouseSkuMap = [];
        $conn = Bd_Db_ConnMgr::getConn('nwms_order/nwms_order_gzns');
        $sql = "SELECT DISTINCT stockin_order_sku.sku_id,stockin_order.warehouse_id
FROM stockin_order_sku
LEFT JOIN stockin_order on stockin_order.stockin_order_id = stockin_order_sku.stockin_order_id
 WHERE stockin_order.stockin_order_type = ".Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT ." AND stockin_order.data_source = ". Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM;
        $arrStockInOrderList = $conn->query($sql);
        foreach ($arrStockInOrderList as $arrStockInOrderInfo) {
            if (!isset($arrWarehouseSkuMap[$arrStockInOrderInfo['warehouse_id']])) {
                $arrWarehouseSkuMap[$arrStockInOrderInfo['warehouse_id']]['status'] = 0;
            }
            $arrWarehouseSkuMap[$arrStockInOrderInfo['warehouse_id']]['sku_id_list'][] = $arrStockInOrderInfo['sku_id'];

        }
        $daoRedis = new Dao_Redis_Common();
        $daoRedis->setNeedFixWarehouseSkuList($arrWarehouseSkuMap);
    }
}
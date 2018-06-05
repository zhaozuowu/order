<?php
/**
 * @name PlaceStockInOrder
 * @desc 自动上架上架单
 * @author hang.song02@ele.me
 */

Bd_Init::init();
class PlaceStockInOrder
{
    public function execute() {
        $sql = 'SELECT DISTINCT
	              stockin_order_id 
                FROM
                  nwms_stock.sku_batch 
                WHERE
	              sku_batch_id IN (
                      SELECT DISTINCT
	                    sku_batch_id 
                      FROM
	                    nwms_stock.stock_location_map 
                      WHERE
	                    area_code = "AR001" 
	                  AND warehouse_id NOT IN ( 1000001, 1000002 ) 
	                  AND total_amount > 0 
	              )';
        $conn = Bd_Db_ConnMgr::getConn('nwms_order/nwms_order_gzns');
        $arrStockInOrderList = $conn->query($sql);
        foreach ($arrStockInOrderList as $arrStockInOrderInfo) {
            $intStockInOrderId = $arrStockInOrderInfo['stockin_order_id'];
            $arrInput['stockin_order_ids'] = $intStockInOrderId;
            $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_PLACE_ORDER_CREATE, $arrInput);
            echo $intStockInOrderId.PHP_EOL;
            if (false == $ret) {
                Bd_Log::warning("send wmq failed arrInput[%s] cmd[%s]",
                    json_encode($arrInput), Order_Define_Cmd::CMD_PLACE_ORDER_CREATE);
            }
        }
    }
}

$obj = new PlaceStockInOrder();
$obj->execute();
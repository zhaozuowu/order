#!php/bin/php
<?php
/**
 * @name DeleteInvalidRedisOrderInfos
 * @desc wipe out cached data in redis which is created for over 24hours that order type is reserve order, stockin order
 *       after data wiped out, an email will be send to order related operator automatically
 * @author wende.chen@ele.me
 */

Bd_Init::init();

try {
    $objWork = new DeleteInvalidRedisOrderInfos();
    $objWork->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}


class DeleteInvalidRedisOrderInfos
{
    /**
     * work
     */
    public function work()
    {
        $intStartTime = strtotime(date('Ymd', strtotime('-1 day')));
        $intEndTime = $intStartTime + Order_Define_Const::UNIX_TIME_SPAN_PER_DAY - 1;

        // Query all reserve order info in a day、
        $arrConditionReserve = [
            'stockin_time' => [
                'between',
                $intStartTime,
                $intEndTime,
            ],
            'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE,
        ];

        // 1: reserve_order_id
        $arrOrderIds = Model_Orm_StockinOrder::findColumn('source_order_id', $arrConditionReserve);

        // query redis and remove existed keys
        $objRedisReserve = new Dao_Redis_StockInOrder();
        $arrRemovedReserveOrderIds = [];
        foreach ($arrOrderIds as $intOrderId) {
            $strRedisKey = Nscm_Define_OrderPrefix::ASN . $intOrderId;

            echo json_encode($objRedisReserve->getOperateRecord($strRedisKey));

            if (!empty($objRedisReserve->getOperateRecord($strRedisKey))) {

                echo 'TEST';

                $arrRemovedReserveOrderIds[] = $intOrderId;
                if (empty($objRedisReserve->dropOperateRecord($strRedisKey))) {
                    Bd_Log::warning('delete redis reserve order [' . $strRedisKey . '] failed');
                }
            }
        }

        if (empty($arrRemovedReserveOrderIds)) {
            Bd_Log::trace('finish process reserve order');
        } else {
            Bd_Log::warning('reserve_order_id_in_redis_found, count:' . count($arrRemovedReserveOrderIds));
            Bd_Log::trace('remove from redis reserve_order_id, value:' . json_encode($arrRemovedReserveOrderIds));
        }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Query all stockin order info in a day
        $intStartTime = strtotime(date('Ymd', strtotime('-1 day')));
        $intEndTime = $intStartTime + Order_Define_Const::UNIX_TIME_SPAN_PER_DAY - 1;

        // conditions include stockin order created by system
        $arrConditionStockin = [
            'stockin_time' => [
                'between',
                $intStartTime,
                $intEndTime,
            ],
            'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM,
            'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RETURN,
            'stockin_order_status' => Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH,
        ];

        // 2: stockin_order_id
        $arrOrderIds = Model_Orm_StockinOrder::findColumn('stockin_order_id', $arrConditionStockin);

        // query redis and remove existed keys
        $objRedisStockin = new Dao_Redis_StockInOrder();
        $arrRemovedStockinOrderIds = [];
        foreach ($arrOrderIds as $intOrderId) {
            $strRedisKey = Nscm_Define_OrderPrefix::ASN . $intOrderId;
            if (!empty($objRedisStockin->getOperateRecord($strRedisKey))) {
                $arrRemovedStockinOrderIds[] = $intOrderId;
                if (empty($objRedisStockin->dropOperateRecord($strRedisKey))) {
                    Bd_Log::warning('delete redis reserve order [' . $strRedisKey . '] failed');
                }
            }
        }

        if (empty($arrRemovedStockinOrderIds)) {
            Bd_Log::trace('finish process stockin order');
        } else {
            Bd_Log::warning('stockin_order_id_in_redis_found, count:' . count($arrRemovedStockinOrderIds));
            Bd_Log::trace('remove from redis stockin_order_id, value:' . json_encode($arrRemovedStockinOrderIds));
        }

        // TODO: send email - TODO


    }
}
#!php/bin/php
<?php
/**
 * @name DeleteInvalidRedisOrderInfos
 * @desc wipe out cached data in redis which is created for over 24hours that order type is reserve order, stockin order
 *       after data wiped out, an email will be send to order related operator automatically
 * @author wende.chen@ele.me
 */

Bd_Init::init();

try
{
    $objWork = new DeleteInvalidRedisOrderInfos();
    $objWork->work();
} catch (Exception $e)
{
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
        // Query all reserve order info in a day
        $arrTimeSpan = [
                'start' => (time() - 84600),
                'end' => time(),
            ];

        $arrCondition = [
            'create_time' => [
                    'between',
                    $arrTimeSpan['start'],
                    $arrTimeSpan['end'],
                ],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];

        // TODO: query id info
        // 1: reserve_order_id
        $arrRecentReserveOrderInfos = [];
        $intAllReserveOrderCount = 0;

        // 2: stockin_order_id
        $arrRecentStockinOrderInfos = [];
        $intAllStockinOrderCount = 0;

        $arrDelReserveOrderKeys = [];
        $arrDelStockinOrderKeys = [];

        // TODO: query redis and remove existed keys

        for ($i = 0; $i <= 100; $i+=10) {
            Order_Util::progressBar($i, 100);
            sleep(1);
        }

        // TODO: send email
    }
}
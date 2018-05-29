<?php
/**
 * @name SyncOrderStatisticsDemotion.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/28 20:04
 */

Bd_Init::init();

try
{
    $objWork = new SyncOrderStatisticsDemotion();
    $objWork->daemon();
} catch (Exception $e)
{
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}
class SyncOrderStatisticsDemotion
{
    /**
     * limit
     */
    const LIMIT = 100;

    /**
     * stop hour
     */
    const STOP_HOUR = 9;

    /**
     * stop minute
     */
    const STOP_MINUTE = 0;

    /**
     * @var Dao_Redis_StatisticsDemotion
     */
    private $daoRedis;

    /**
     * @var Service_Data_Statistics_Statistics
     */
    private $dataStatistics;

    /**
     * SyncOrderStatisticsDemotion constructor.
     */
    function __construct()
    {
        $this->daoRedis = new Dao_Redis_StatisticsDemotion();
        $this->dataStatistics = new Service_Data_Statistics_Statistics();
    }

    /**
     * daemon
     */
    public function daemon()
    {
        while (true) {
            try {
                $intResult = $this->work();
                if (!empty($intResult)) {
                    Bd_Log::trace($intResult . 'orders treated, sleep 1 sec');
                    sleep(1);
                } else {
                    Bd_Log::trace('waiting result not found, sleep 5 sec');
                    sleep(5);
                }
                $intCurrentHour = intval(date('G'));
                $intCurrentMinute = intval(date('i'));
                if (self::STOP_HOUR == $intCurrentHour && self::STOP_MINUTE == $intCurrentMinute) {
                    Bd_Log::trace('exit, current time: ' . date('Y-m-d H:i:s'));
                    return;
                }

            } catch (Exception $e) {
                Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]', __FILE__, $e->getCode(), $e->getMessage()));
            }
        }
    }

    /**
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    private function work()
    {
        // once deal 100 pieces
        $intLimit = self::LIMIT;
        $intCount = 0;
        for ($i = 0; $i < $intLimit; $i++) {
            $arrOrderInfo = $this->daoRedis->getOnePieceAndDrop();
            if (empty($arrOrderInfo)) {
                return $intCount;
            }
            $intTable = $arrOrderInfo['table'];
            $intType = $arrOrderInfo['order_type'];
            $intOrderId = $arrOrderInfo['order_id'];
            Bd_Log::trace('current order info: ' . json_encode($arrOrderInfo));
            if (Order_Statistics_Type::ACTION_CREATE == $intType) {
                $this->dataStatistics->addOrderStatistics($intOrderId, $intTable);
            } else if (Order_Statistics_Type::ACTION_UPDATE == $intType) {
                $this->dataStatistics->updateOrderStatistics($intOrderId, $intTable);
            } else {
                continue;
            }
            Bd_Log::trace('order finish! order id: ' . $intOrderId);
            $intCount++;
        }
        return $intCount;
    }
}
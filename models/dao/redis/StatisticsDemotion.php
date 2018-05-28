<?php
/**
 * @name Dao_Redis_StatisticsDemotion
 * @desc
 * @author: bochao.lv@ele.me
 */

class Dao_Redis_StatisticsDemotion extends Order_Base_Redis
{
    const STATISTICS_DEMOTION_KEY = 'nwms:statistics:demotion:key';

    const REDIS_LIST_RANGE_FORWARD = -1;

    /**
     * 增加统计单号，order type 和 table参数，从Order_Statistics_Type中获取
     * @param int $intOrderId 订单号
     * @param int $intOrderType 订单类型
     * @param int $intTable 表类型
     * @return int
     * @throws Order_Error
     */
    public function addStatisticsOrder($intOrderId, $intOrderType, $intTable)
    {
        $arrInput = [
            'order_id' => $intOrderId,
            'order_type' => $intOrderType,
            'table' => $intTable,
        ];
        $jsonInput = json_encode($arrInput);
        Bd_Log::debug(sprintf('rPush to redis, key[%s], value [%s]', self::STATISTICS_DEMOTION_KEY, $jsonInput));
        $intResult = $this->objRedisConn->rPush(self::STATISTICS_DEMOTION_KEY, $jsonInput);
        if (empty($intResult)) {
            Bd_Log::warning('add_statistics_order_write_redis_fail!');
            Order_Error::throwException(Order_Error_Code::REDIS_OPERATE_FAILED);
        }
        return $intResult;
    }

    /**
     * @param $intLimit
     * @return array
     */
    public function getStatisticsOrder($intLimit)
    {
        $arrResultFromRedis = $this->objRedisConn->lRange(self::STATISTICS_DEMOTION_KEY, 0, $intLimit - 1);
        $arrResult = array_map(function ($jsonStatisticsInfo) {
            return json_decode($jsonStatisticsInfo, true);
        }, $arrResultFromRedis);
        return $arrResult;
    }

    /**
     * @param $intLimit
     * @return array
     */
    public function dropStatisticsOrder($intLimit)
    {
        return $this->objRedisConn->lTrim(self::STATISTICS_DEMOTION_KEY, $intLimit, self::STATISTICS_DEMOTION_KEY);
    }
}
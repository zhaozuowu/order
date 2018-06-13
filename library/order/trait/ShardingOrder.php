<?php
/**
 * 分表策略
 * @authors 姜东起 (jiangdongqi@iwaimai.baidu.com)
 * @date    2017-12-04 13:50:11
 * @version $Id$
 */
trait Order_Trait_ShardingOrder
{
    /**
     * 根据键值确定表名
     *
     * @param      string    $shardingKey    分表键
     * @param      <type>    $shardingValue  键值
     *
     * @throws     Wm_Error  (description)
     *
     * @return     <type>    分表名
     */
    public static function shardingRule($shardingKey, $shardingValue)
    {
        if ($shardingKey === 'create_time') {
            $time = intval($shardingValue);
        } else {
            error_log(
                sprintf(
                    "CLASS[%s] FUNCTION[%s] shardingKey illegal, shardingKey=[%s]",
                    __CLASS__,
                    __FUNCTION__,
                    @json_encode($shardingKey)
                )
            );
            throw new Nscm_Exception_Error('shardingKey illegal');
        }
        $suffix = date('Ym', $time);
        $tableName = sprintf("%s_%s", static::$tableName, $suffix);
        return $tableName;
    }

    /**
     * 生成区间表名
     *
     * @param      string    $shardingKey       The sharding key
     * @param      <type>    $minShardingValue  The minimum sharding value
     * @param      <type>    $maxShardingValue  The maximum sharding value
     *
     * @throws     Wm_Error  (description)
     *
     * @return     array     ( description_of_the_return_value )
     */
    public static function shardingRuleRange($shardingKey, $minShardingValue, $maxShardingValue)
    {
        if ($shardingKey !== 'create_time') {
            error_log(
                sprintf(
                    "CLASS[%s] FUNCTION[%s] shardingKey illegal, shardingKey=[%s]",
                    __CLASS__,
                    __FUNCTION__,
                    @json_encode($shardingKey)
                )
            );
            throw new Nscm_Exception_Error('shardingKey illegal');
        }
        $minTime = intval($minShardingValue);
        $maxTime = intval($maxShardingValue);
        $tables = [];
        while (date('Ym',$minTime) <= date('Ym',$maxTime)) {
            $tableName = sprintf("%s_%s", static::$tableName, date('Ym', $maxTime));
            $tables[] = $tableName;
            $maxTime = strtotime('-1 month', $maxTime);
        }
        return $tables;
    }
}

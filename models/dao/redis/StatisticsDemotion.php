<?php
/**
 * @name Dao_Redis_StatisticsDemotion
 * @desc
 * @author: bochao.lv@ele.me
 */

class Dao_Redis_StatisticsDemotion extends Order_Base_Redis
{
    /**
     * 增加统计单号，order type 和 table参数，从Order_Statistics_Type中获取
     * @param int $intOrderId 订单号
     * @param int $intOrderType 订单类型
     * @param int $intTable 表类型
     */
    public function addStatisticsOrder($intOrderId, $intOrderType, $intTable)
    {

    }
}
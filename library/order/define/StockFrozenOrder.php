<?php
/**
 * @name Order_Define_StockFrozenOrder
 * @desc 冻结单常量
 * @author sunzhixin@iwaimai.baidu.com
 */

class Order_Define_StockFrozenOrder
{
    /**
     * 单次冻结SKU上限
     * @var int
     */
    const STOCK_FROZEN_ORDER_MAX_SKU = 100;

    /**
     * 冻结
     */
    const FROZEN_ORDER_STATUS_FROZEN = 1;

    /**
     * 部分冻结
     */
    const FROZEN_ORDER_STATUS_PART_FROZEN = 2;

    /**
     * 关闭
     */
    const FROZEN_ORDER_STATUS_CLOSED = 3;

    /**
     * 冻结单状态
     * @var array
     */
    const FROZEN_ORDER_STATUS_MAP = [
        self::FROZEN_ORDER_STATUS_FROZEN        => '冻结',
        self::FROZEN_ORDER_STATUS_PART_FROZEN   => '部分冻结',
        self::FROZEN_ORDER_STATUS_CLOSED        => '关闭',
    ];


    /**
     * 人工创建
     */
    const FROZEN_ORDER_CREATE_BY_USER = 1;

    /**
     * 系统创建
     */
    const FROZEN_ORDER_CREATE_BY_SYSTEM = 2;

    /**
     * 订单创建来源
     * @var array
     */
    const FROZEN_ORDER_CREATE_MAP = [
        self::FROZEN_ORDER_CREATE_BY_USER       => '人工创建',
        self::FROZEN_ORDER_CREATE_BY_SYSTEM     => '系统创建',
    ];

    const FROZEN_ORDER_BY_SYSTEM_REMARK = '系统自动创建冻结单';
}
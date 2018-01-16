<?php
/**
 * @name Order_Define_ReserveOrder
 * @desc Order_Define_ReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Define_ReserveOrder
{
    /**
     * 待入库
     * @var int
     */
    const STATUS_STOCKING = 10;

    /**
     * 已入库
     * @var int
     */
    const STATUS_STOCKED = 20;

    /**
     * 人工作废
     * @var int
     */
    const STATUS_DUPLICATE_HUMAN = 30;

    /**
     * 超时作废
     * @var int
     */
    const STATUS_DUPLICATE_TIME = 31;

    /**
     * 全部状态
     * @var array
     */
    const ALL_STATUS = [
        self::STATUS_STOCKING => true,
        self::STATUS_STOCKED => true,
        self::STATUS_DUPLICATE_HUMAN => true,
        self::STATUS_DUPLICATE_TIME => true,
    ];

    /**
     * 彩云作废
     * @var array
     */
    const NSCM_DESTROY_STATUS = [
        1 => self::STATUS_DUPLICATE_HUMAN,
        2 => self::STATUS_DUPLICATE_TIME,
    ];

    /**
     * 允许作废的状态
     * @var array
     */
    const ALLOW_DESTROY = [
        self::STATUS_STOCKING => true,
    ];

    /**
     * 允许入库的状态
     * @var array
     */
    const ALLOW_STOCKIN = [
        self::STATUS_STOCKING => true,
    ];
}
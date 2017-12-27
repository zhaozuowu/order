<?php
/**
 * @name Order_Define_PurchaseOrder
 * @desc Order_Define_PurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Define_PurchaseOrder
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
}
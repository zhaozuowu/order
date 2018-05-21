<?php
/**
 * @name ${NAME}
 * @desc ${desc}
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Order_Define_PlaceOrder
{
    /**
     * 上架单状态
     */
    const STATUS_ALL = 0; //全部
    const STATUS_WILL_PLACE = 10;   // 待上架
    const STATUS_PLACING = 20;      // 上架中
    const STATUS_PLACED = 30;       // 已上架

    /**
     * 上架单状态描述
     */
    const PLACE_ORDER_STATUS_SHOW = [
        self::STATUS_ALL        => '全部',
        self::STATUS_WILL_PLACE => '待上架',
        self::STATUS_PLACING    => '上架中',
        self::STATUS_PLACED     => '已上架',
    ];

    /**
     * 上架单质量为非良品
     * @var integer
     */
    const PLACE_ORDER_QUALITY_BAD = 2;

    /**
     * 上架单质量为良品
     * @var integer
     */
    const PLACE_ORDER_QUALITY_GOOD = 1;

    /**
     * 上架单质量数组
     * @var array
     */
    const PLACE_ORDER_QUALITY_MAP = [
        self::PLACE_ORDER_QUALITY_GOOD => '良品',
        self::PLACE_ORDER_QUALITY_BAD => '非良品',
    ];

    /**
     * 全部状态
     */
    const ALL_STATUS = [
        self::STATUS_WILL_PLACE,
        self::STATUS_PLACING,
        self::STATUS_PLACED,
    ];
}
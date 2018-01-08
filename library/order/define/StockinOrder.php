<?php
/**
 * @name Order_Define_StockinOrder
 * @desc Order_Define_StockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Define_StockinOrder
{
    /**
     * type reserve
     * @var int
     */
    const STOCKIN_ORDER_TYPE_RESERVE = 1;

    /**
     * type stock out
     * @var int
     */
    const STOCKIN_ORDER_TYPE_STOCKOUT = 2;

    /**
     * type return
     * @var int
     */
    const STOCKIN_ORDER_TYPE_RETURN = 3;

    /**
     * type reserve
     * @var array
     */
    const STOCKIN_ORDER_TYPES = [
        self::STOCKIN_ORDER_TYPE_RESERVE => true,
        self::STOCKIN_ORDER_TYPE_STOCKOUT => true,
//        self::STOCKIN_ORDER_TYPE_RETURN => true,
    ];

    /**
     * finish stock in
     * @var int
     */
    const STOCKIN_ORDER_STATUS_FINISH = 30;

    /**
     * stock in sku exp date max
     */
    const STOCKIN_SKU_EXP_DATE_MAX = 2;

    /**
     * status map
     * @var
     */
    const STOCKIN_STATUS_MAP = [
        self::STOCKIN_ORDER_STATUS_FINISH => '已完成',
    ];
}
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
     * type stock out
     * @var int
     */
    const STOCKIN_ORDER_TYPE_SYS = 3;

    /**
     * type return
     * @var int
     */
    const STOCKIN_ORDER_TYPE_RETURN = 3;

    /**
     * nscm sure stock in
     * @var int
     */
    const NSCM_SURE_STOCKIN = 1;

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
     * waiting stock in
     * @var int
     */
    const STOCKIN_ORDER_STATUS_WAITING = 10;
    /**
     * waiting stock in
     * @var int
     */
    const STOCKIN_ORDER_STATUS_CANCEL = 20;

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

    /**
     * 采购单类型
     * @var
     */
    const  STOCKIN_ORDER_TYPE_MAP = [
        self::STOCKIN_ORDER_TYPE_RESERVE => '采购入库',
        self::STOCKIN_ORDER_TYPE_STOCKOUT => '销退入库'
    ];

    /**
     * 销退入库SKU原因
     * 全部拒收
     */
    const STOCKIN_ORDER_SKU_RETURN_REASON_ALL_REJECT = 1;
    /**
     * 销退入库SKU原因
     * 部分拒收
     */
    const STOCKIN_ORDER_SKU_RETURN_REASON_PART_REJECT = 2;
    /**
     * 销退入库SKU原因
     * 下架 = 汰换
     */
    const STOCKIN_ORDER_SKU_RETURN_REASON_OFF_SHELF = 4;

    const STOCKIN_ORDER_SKU_RETURN_REASON_MAP = [
        self::STOCKIN_ORDER_SKU_RETURN_REASON_ALL_REJECT => '全部拒收',
        self::STOCKIN_ORDER_SKU_RETURN_REASON_PART_REJECT => '部分拒收',
        self::STOCKIN_ORDER_SKU_RETURN_REASON_OFF_SHELF => '汰换',
    ];
}
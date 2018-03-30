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
     * 销退入库原因定义
     */
    const STOCKIN_STOCKOUT_REASON_REJECT_ALL        = 1;    // 整单拒收
    const STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT    = 2;    // 部分拒收
    const STOCKIN_STOCKOUT_REASON_CHANGE            = 4;    // 汰换
    const STOCKIN_STOCKOUT_REASON_REMOVE_SITE       = 8;    // 撤点
    const STOCKIN_STOCKOUT_REASON_RETURNED          = 16;   // 退货

    /**
     * 销退入库原因映射表
     */
    const STOCKIN_STOCKOUT_REASON_MAP = [
        self::STOCKIN_STOCKOUT_REASON_REJECT_ALL        => '整单拒收',
        self::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT    => '部分拒收',
        self::STOCKIN_STOCKOUT_REASON_CHANGE            => '汰换',
        self::STOCKIN_STOCKOUT_REASON_REMOVE_SITE       => '撤点',
        self::STOCKIN_STOCKOUT_REASON_RETURNED          => '退货',
    ];

}
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
        self::STOCKIN_ORDER_STATUS_CANCEL => '已作废',
        self::STOCKIN_ORDER_STATUS_WAITING => '待入库',
    ];

    /**
     * 采购单类型
     * @var
     */
    const  STOCKIN_ORDER_TYPE_MAP = [
        self::STOCKIN_ORDER_TYPE_RESERVE => '采购入库',
        self::STOCKIN_ORDER_TYPE_STOCKOUT => '销退入库',
    ];
    /**
     * 销退入库原因定义
     */
    const STOCKIN_STOCKOUT_REASON_REJECT_ALL        = 1;    // 整单拒收
    const STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT    = 2;    // 部分拒收
    const STOCKIN_STOCKOUT_REASON_CHANGE            = 4;    // 下架
    const STOCKIN_STOCKOUT_REASON_REMOVE_SITE       = 8;    // 撤点
    const STOCKIN_STOCKOUT_REASON_RETURNED          = 16;   // 退货

    /**
     * 销退入库原因类型定义
     */
    const STOCKIN_STOCKOUT_REASON_DEFINE = [
        self::STOCKIN_STOCKOUT_REASON_REJECT_ALL        => true,
        self::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT    => true,
        self::STOCKIN_STOCKOUT_REASON_CHANGE            => true,
        self::STOCKIN_STOCKOUT_REASON_REMOVE_SITE       => true,
        self::STOCKIN_STOCKOUT_REASON_RETURNED          => true,
    ];

    /**
     * 销退入库原因映射表
     */
    const STOCKIN_STOCKOUT_REASON_MAP = [
        self::STOCKIN_STOCKOUT_REASON_REJECT_ALL        => '整单拒收',
        self::STOCKIN_STOCKOUT_REASON_PARTIAL_REJECT    => '部分拒收',
        self::STOCKIN_STOCKOUT_REASON_CHANGE            => '下架',
        self::STOCKIN_STOCKOUT_REASON_REMOVE_SITE       => '撤点',
        self::STOCKIN_STOCKOUT_REASON_RETURNED          => '退货',
    ];

    //入库单打印状态，未打印
    const STOCKIN_ORDER_NOT_PRINT = 1;
    //入库单打印状态，已打印
    const STOCKIN_ORDER_IS_PRINT = 2;

    /**
     * 入库单打印状态列表
     * @var array
     */
    const STOCKIN_PRINT_STATUS = [
        self::STOCKIN_ORDER_NOT_PRINT => '未打印',
        self::STOCKIN_ORDER_IS_PRINT => '已打印',
    ];

    /**
     * 入库单状态
     */
    const STOCKIN_ORDER_STATUS_WAIT             = 10;   // 待入库
    const STOCKIN_ORDER_STATUS_DESTROYED        = 20;   // 已作废
    const STOCKIN_ORDER_STATUS_FINISHED         = 30;   // 已完成

    /**
     * 入库单状态类型定义
     */
    const STOCKIN_ORDER_STATUS_DEFINE = [
        self::STOCKIN_ORDER_STATUS_WAIT         => true,
        self::STOCKIN_ORDER_STATUS_DESTROYED    => true,
        self::STOCKIN_ORDER_STATUS_FINISHED     => true,
    ];

    /**
     * 入库单状态文本映射表
     */
    const STOCKIN_ORDER_STATUS_MAP = [
        self::STOCKIN_ORDER_STATUS_WAIT         => '待入库',
        self::STOCKIN_ORDER_STATUS_DESTROYED    => '已作废',
        self::STOCKIN_ORDER_STATUS_FINISHED     => '已完成',
    ];

    /**
     * 订单来源类型
     */
    const STOCKIN_DATA_SOURCE_FROM_SYSTEM   = 1;    // 系统对接
    const STOCKIN_DATA_SOURCE_MANUAL_CREATE = 2;    // 手工创建

    /**
     * 订单数据来源类型定义
     */
    const STOCKIN_DATA_SOURCE_DEFINE = [
        self::STOCKIN_DATA_SOURCE_FROM_SYSTEM   => 1,
        self::STOCKIN_DATA_SOURCE_MANUAL_CREATE => 2,
    ];

    /**
     * 订单数据来源类型文本映射
     */
    const STOCKIN_DATA_SOURCE_MAP = [
        self::STOCKIN_DATA_SOURCE_FROM_SYSTEM   => '系统对接',
        self::STOCKIN_DATA_SOURCE_MANUAL_CREATE => '手工创建',
    ];

    /**
     * 已生成上架单
     * @var integer
     */
    const STOCKIN_IS_PLACED = 2;

    /**
     * 未生成上架单
     * @var integer
     */
    const STOCKIN_NOT_PLACED = 1;

    /**
     * 自动上架
     * @var integer
     */
    const STOCKIN_AUTO_PLACED = 3;

    /**
     * 是否生成上架单map
     * @var array
     */
    const STOCKIN_IS_PLACED_MAP = [
        self::STOCKIN_IS_PLACED => '已生成',
        self::STOCKIN_NOT_PLACED => '未生成',
        self::STOCKIN_AUTO_PLACED => '---',
    ];
}
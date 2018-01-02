<?php

/**
 * @name Order_Define_StockoutOrder
 * @desc 出库单常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_StockoutOrder
{
    /**
     * 出库单状态列表
     */
    const INIT_STOCKOUT_ORDER_STATUS = 10;//待审核
    const STAY_PICKING_STOCKOUT_ORDER_STATUS = 20;//待拣货
    const STAY_RECEIVED_STOCKOUT_ORDER_STATUS = 25;//待揽收
    const STOCKOUTED_STOCKOUT_ORDER_STATUS = 30;//已出库
    /**
     * 出库单状态列表
     * @var array
     */
    const STOCK_OUT_ORDER_STATUS_LIST = [
        '10' => '待审核',
        '20' => '待拣货',
        '25' => '待揽收',
        '30' => '已出库',
    ];

    /**
     * 出库单类型
     */
    const STOCKOUT_ORDER_TYPE_STOCKOUT = 1;
    const STOCKOUT_ORDER_TYPE_SUPPLY = 2;

    /**
     * 出库单类型列表
     * @var array
     */
    const STOCKOUT_ORDER_TYPE_LIST = [
        self::STOCKOUT_ORDER_TYPE_STOCKOUT => '订单出货',
        self::STOCKOUT_ORDER_TYPE_SUPPLY => '货架补货',
    ];
    /**
     * 出库单打印状态列表
     */
    const STOCKOUT_ORDER_NOT_PRINT = 1;
    const STOCKOUT_ORDER_IS_PRINT = 2;
    /**
     * 出库单打印状态
     * @var array
     */
    const STOCKOUT_PRINT_STATUS = [
        self::STOCKOUT_ORDER_NOT_PRINT => '未打印',
        self::STOCKOUT_ORDER_NOT_PRINT => '已打印',
    ];

}
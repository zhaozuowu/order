<?php

/**
 * @name Order_Define_StockoutOrder
 * @desc 出库单常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_ShiftOrder
{
    /**
     * 移位单状态列表
     */
    const SHIFT_ORDER_STATUS_CREATE = 1;//待移位
    const SHIFT_ORDER_STATUS_FINISH = 2;//移位完成
    const SHIFT_ORDER_STATUS_CANCEL = 3;//已取消


    /**
     * 移位单状态列表
     * @var array
     */
    const SHIFT_ORDER_STATUS_LIST = [
        SELF::SHIFT_ORDER_STATUS_CREATE => '待移位',
        SELF::SHIFT_ORDER_STATUS_FINISH => '移位完成',
        SELF::SHIFT_ORDER_STATUS_CANCEL => '已取消',
    ];

}

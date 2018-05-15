<?php

/**
 * @name Order_Define_PickupOrder
 * @desc 拣货单常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_PickupOrder
{

    /**
     * 拣货单状态
     */
    const PICKUP_ORDER_STATUS_INIT = 10;//待拣货
    const PICKUP_ORDER_STATUS_FINISHED= 20;//拣货完成
    const PICKUP_ORDER_STATUS_CANCEL = 30;//已取消

    /**
     * 拣货单状态列表
     * @var array
     */
    const PICKUP_ORDER_STATUS_MAP = [
        '10' => '待拣货',
        '20' => '拣货完成',
        '30' => '已取消',
    ];
    /**
     * 拣货单类型
     */
    const PICKUP_ORDER_TYPE_NOT_SPLIT = 1;
    const PICKUP_ORDER_TYPE_ORDER = 2;
    /**
     * @desc 拣货单类型映射
     * @var array
     */
    const PICKUP_ORDER_TYPE_MAP = [
        self::PICKUP_ORDER_TYPE_NOT_SPLIT => '汇总拣货',
        self::PICKUP_ORDER_TYPE_ORDER => '订单拣货',
    ];

    /**
     * 未打印
     * @var integer
     */
    const PICKUP_ORDER_NOT_PRINTED = 1;

    /**
     * 已打印
     * @var integer
     */
    const PICKUP_ORDER_PRINTED = 2;

    /**
     * 打印状态解析映射
     */
    const PICKUP_ORDER_PRINT_MAP = [
        self::PICKUP_ORDER_NOT_PRINTED => '未打印',
        self::PICKUP_ORDER_PRINTED => '已打印',
    ];

    /**
     * 打印状态
     * @var array
     */
    const PICKUP_ORDER_PRINT_STATUS = [
        self::PICKUP_ORDER_NOT_PRINTED,
        self::PICKUP_ORDER_PRINTED,
    ];
}
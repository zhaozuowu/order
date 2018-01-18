<?php

/**
 * @name Order_Define_BusinessFormOrder
 * @desc 业态出库订常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_BusinessFormOrder
{
    /**
     * 业态订单创建成功
     * @var integer
     */
    const BUSINESS_FORM_ORDER_SUCCESS = 1;

    /**
     * 业态订单创建失败
     * @var integer
     */
    const BUSINESS_FORM_ORDER_FAILED = 2;

    /**
     * 业态订单状态列表
     * @var array
     */
    const BUSINESS_FORM_ORDER_STATUS_LIST = [
        '1' => '成功',
        '2' => '失败',
    ];

    /**
     * 业态订单类型列表
     * @var array
     */
    const BUSINESS_FORM_ORDER_TYPE_LIST = [
        '1' => '无人货架',
        '2' => '前置仓',
        '3' => '便利店',
    ];

    /**
     * 补货类型为铺货
     * @var integer
     */
    const ORDER_SUPPLY_TYPE_CREATE = 1;

    /**
     * 补货类型为补货
     * @var integer
     */
    const ORDER_SUPPLY_TYPE_SUPPLY = 2;

    /**
     * 补货类型
     * @var array
     */
    const ORDER_SUPPLY_TYPE = [
        self::ORDER_SUPPLY_TYPE_CREATE => '铺货',
        self::ORDER_SUPPLY_TYPE_SUPPLY => '补货',
    ];

    /**
     * 携带设备类型为货架
     * @var integer
     */
    const ORDER_DEVICE_TYPE_SHELF = 1;

    /**
     * 携带设备类型为冰柜
     * @var integer
     */
    const ORDER_DEVICE_TYPE_REFRIGERATOR = 2;

    /**
     * 携带设备类型
     * @var array
     */
    const ORDER_DEVICE_MAP = [
        self::ORDER_DEVICE_TYPE_SHELF => '货架',
        self::ORDER_DEVICE_TYPE_REFRIGERATOR => '冰柜',
    ];




}
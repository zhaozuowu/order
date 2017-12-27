<?php

/**
 * @name Order_Define_BusinessFormOrder
 * @desc 业态出库订常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_BusinessFormOrder
{
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
     * 业务烈性
     * @var array
     */
    const ORDER_SUPPLY_TYPE = [
        '1' => '铺货',
        '2' => '补货',
        '3' => '补货+盘货',
    ];


}
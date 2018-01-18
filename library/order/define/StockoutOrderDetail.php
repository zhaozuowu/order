<?php

/**
 * @name Order_Define_StockoutOrderDetail
 * @desc Order_Define_StockoutOrderDetail
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_StockoutOrderDetail
{

    /**
     * 出库单状态映射
     * @var array
     */
    const  STOCKOUT_ORDER_STATUS_MAP = [
        25 => 2,
        30 => 3,
        20 =>1,
        10 =>1,
        50 =>4
    ];

    /**
     * 出库单状态映射
     * @var array
     */
    const STOCKOUT_ORDER_STATUS_TEXT_MAP = [
        '10' => '待拣货',
        '20' => '待拣货',
        '25' => '待揽收',
        '30' => '已出库',
        '50' => '已作废',
    ];

    /**
     * 出库单类型
     * @var array
     */
    const  STOCKOUT_ORDER_TYPE_MAP = [
        '1'=>'订单出库',
        '2' =>'采购退货',
        '3' =>'配货出库',
    ];
    /**
     * 订单类型
     */
    const  STOCKOUT_ORDER_SOURCE_MAP = [
        '1'=>'货架补货',
        '2'=>'前置仓',
        '3'=>'便利店',
    ];

    /**
     * 是否进口
     */
    const  IMPORT_MAP = [
        '1' => '否',
        '2' =>'是',
    ];

    /**
     * 效期类型
     */
    const  SKU_EFFECT_TYPE = [
        '1' => '生产日期',
        '2' =>'失效日期',
    ];



}
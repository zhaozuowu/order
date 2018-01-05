<?php
/**
 * @name Order_Define_ReserveOrder
 * @desc Order_Define_ReserveOrder
 * @author chenwende@iwaimai.baidu.com
 */

class Order_Define_StatisticsForm
{
    /**
     * 输出类型-立即展示
     */
    const OUTPUT_TYPE_DISPLAY_INSTANTLY = 1;

    /**
     * 输出类型-添加到任务
     */
    const OUTPUT_TYPE_DISPATCH_TASK = 2;

    /**
     * type output_type
     * @var array
     */
    const ALL_STATUS = [
        self::OUTPUT_TYPE_DISPLAY_INSTANTLY => true,
        self::OUTPUT_TYPE_DISPATCH_TASK => true,
    ];
}
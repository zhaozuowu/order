<?php
/**
 * @name Order_Statistics_Table
 * @desc 表信息
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Statistics_Table
{

    /**
     * source order stockin
     * @var string
     */
    const SOURCE_ORM_STOCKIN = 'Model_Orm_StockinOrder';

    /**
     * source order stockin sku
     * @var string
     */
    const SOURCE_ORM_STOCKIN_SKU = 'Model_Orm_StockinOrderSku';

    /**
     * source order stockout
     * @var string
     */
    const SOURCE_ORDER_STOCKOUT = 'Model_Orm_StockoutOrder';

    /**
     * source order stockout sku
     * @var string
     */
    const SOURCE_ORDER_STOCKOUT_SKU = 'Model_Orm_StockoutOrderSku';

    /**
     * orm table
     * @var array
     */
    const ORM_TABLE = [
        Order_Statistics_Type::TABLE_STOCKIN_RESERVE => [
            'master' => self::SOURCE_ORM_STOCKIN,
            'master_key' => 'stockin_order_id',
            'slave' => self::SOURCE_ORM_STOCKIN_SKU,
            'slave_key' => 'stockin_order_id',
            'slave_sku' => 'sku_id',
        ],
        Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT => [
            'master' => self::SOURCE_ORM_STOCKIN,
            'master_key' => 'stockin_order_id',
            'slave' => self::SOURCE_ORM_STOCKIN_SKU,
            'slave_key' => 'stockin_order_id',
            'slave_sku' => 'sku_id',
        ],
        Order_Statistics_Type::TABLE_STOCKOUT_ORDER => [
            'master' => self::SOURCE_ORDER_STOCKOUT,
            'master_key' => 'stockout_order_id',
            'slave' => self::SOURCE_ORDER_STOCKOUT_SKU,
            'slave_key' => 'stockout_order_id',
            'slave_sku' => 'sku_id',
        ],
    ];

    /**
     * orm dist
     * @var array
     */
    const ORM_DIST = [
        Order_Statistics_Type::TABLE_STOCKIN_RESERVE => 'Model_Orm_StockinReserveDetail',
        Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT => 'Model_Orm_StockinStockoutDetail',
        Order_Statistics_Type::TABLE_STOCKOUT_ORDER => 'Model_Orm_StockoutOrderDetail',
    ];

}
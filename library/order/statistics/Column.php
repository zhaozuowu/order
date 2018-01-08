<?php
/**
 * @name Order_Statistics_Column
 * @desc 列信息
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Statistics_Column
{
    /**
     * replace text
     * @var string
     */
    const REPLACE = '###';

    /**
     * stockin reserve
     * @var array
     */
    const STOCKIN_RESERVE = [
        'master' => [
            'stockin_order_id',
            'source_order_id',
            'city_name',
            'city_id',
            'warehouse_name',
            'warehouse_id',
            'stockin_order_type',
            'reserve_order_plan_time',
            'reserve_order_plan_time_text' => [
                'type' => Order_Statistics_Type::FUNCTION,
                'function' => 'date',
                'params' => [
                    'Y-m-d H:i:s',
                    self::REPLACE,
                ],
                'replace' => 'reserve_order_plan_time',
            ],
            'stockin_time',
            'stockin_time_text' => [
                'type' => Order_Statistics_Type::FUNCTION,
                'function' => 'date',
                'params' => [
                    'Y-m-d H:i:s',
                    self::REPLACE,
                ],
                'replace' => 'stockin_time',
            ],
            'stockin_batch_id',
            'stockin_order_status',
            'stockin_order_status_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockinOrder::STOCKIN_STATUS_MAP',
                'replace' => 'stockin_order_status',
            ],
            'vendor_name' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'vendor_name',
            ],
            'vendor_id' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'vendor_id',
            ],
        ],
        'slave' => [
            'sku_id',
            'upc_id',
            'sku_name',
            'sku_net',
            'sku_net_unit',
            'sku_net_unit_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::SKU_NET_MAP',
                'replace' => 'sku_net_unit',
            ],
            'sku_net_gram',
            'upc_unit',
            'upc_unit_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::UPC_UNIT_MAP',
                'replace' => 'upc_unit',
            ],
            'sku_effect_type',
            'upc_unit_num',
            'sku_price',
            'sku_price_tax',
        ],
        'sku' => [
            'sku_category_1',
            'sku_category_2',
            'sku_category_3',
            'sku_from_country',
            'sku_from_country_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::SKU_FROM_COUNTRY_MAP',
                'replace' => 'sku_from_country',
            ],
            'sku_category_1_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 0,
                'replace' => 'sku_category_text',
            ],
            'sku_category_2_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 1,
                'replace' => 'sku_category_text',
            ],
            'sku_category_3_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 2,
                'replace' => 'sku_category_text',
            ],
        ],
        'split' => [
            'key' => 'stockin_order_sku_extra_info',
            'columns' => [
                'stockin_order_sku_total_price' => [
                    'amount',
                    'sku_price',
                ],
                'stockin_order_sku_total_price_tax' => [
                    'amount',
                    'sku_price_tax',
                ],
                'stockin_order_real_amount' => 'amount',
                'expire_date',
            ],
        ],
    ];

    /**
     * stockin stockout
     * @var array
     */
    const STOCKIN_STOCKOUT = [
        'master' => [
            'stockin_order_id',
            'source_order_id',
            'city_name',
            'city_id',
            'warehouse_name',
            'warehouse_id',
            'stockin_order_type',
            'reserve_order_plan_time',
            'reserve_order_plan_time_text' => [
                'type' => Order_Statistics_Type::FUNCTION,
                'function' => 'date',
                'params' => [
                    'Y-m-d H:i:s',
                    self::REPLACE,
                ],
                'replace' => 'reserve_order_plan_time',
            ],
            'stockin_time',
            'stockin_time_text' => [
                'type' => Order_Statistics_Type::FUNCTION,
                'function' => 'date',
                'params' => [
                    'Y-m-d H:i:s',
                    self::REPLACE,
                ],
                'replace' => 'stockin_time',
            ],
            'stockin_batch_id',
            'stockin_order_status',
            'stockin_order_status_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockinOrder::STOCKIN_STATUS_MAP',
                'replace' => 'stockin_order_status',
            ],
            'client_name' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'client_name',
            ],
            'client_id' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'client_id',
            ],
            'client_contact' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'client_contact',
            ],
            'client_mobile' => [
                'type' => Order_Statistics_Type::JSON,
                'replace' => 'source_info',
                'key' => 'client_mobile',
            ],
        ],
        'slave' => [
            'sku_id',
            'upc_id',
            'sku_name',
            'sku_net',
            'sku_net_unit',
            'sku_net_unit_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::SKU_NET_MAP',
                'replace' => 'sku_net_unit',
            ],
            'sku_net_gram',
            'upc_unit',
            'upc_unit_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::UPC_UNIT_MAP',
                'replace' => 'upc_unit',
            ],
            'sku_effect_type',
            'upc_unit_num',
            'sku_price',
            'sku_price_tax',
        ],
        'sku' => [
            'sku_category_1',
            'sku_category_2',
            'sku_category_3',
            'sku_from_country',
            'sku_from_country_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::SKU_FROM_COUNTRY_MAP',
                'replace' => 'sku_from_country',
            ],
            'sku_category_1_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 0,
                'replace' => 'sku_category_text',
            ],
            'sku_category_2_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 1,
                'replace' => 'sku_category_text',
            ],
            'sku_category_3_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 2,
                'replace' => 'sku_category_text',
            ],
        ],
        'split' => [
            'key' => 'stockin_order_sku_extra_info',
            'columns' => [
                'stockin_order_sku_total_price' => [
                    'amount',
                    'sku_price',
                ],
                'stockin_order_sku_total_price_tax' => [
                    'amount',
                    'sku_price_tax',
                ],
                'stockin_order_real_amount' => 'amount',
                'expire_date',
            ],
        ],
    ];

    /**
     * stockout order
     * @var array
     */
    /**
     * stockout order
     * @var array
     */
    const STOCKOUT_ORDER = [
        'master' => [
            'stockout_order_id',
            'business_form_order_id',
            'stockout_order_status' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockoutOrderDetail::STOCKOUT_ORDER_STATUS_MAP',
            ],
            'stockout_order_status_describle' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockoutOrderDetail::STOCKOUT_ORDER_STATUS_TEXT_MAP',
                'replace' => 'stockout_order_status',
            ],
            'city_name' => 'customer_city_name',
            'city_id' => 'customer_city_id',
            'warehouse_id',
            'warehouse_name',
            'stockout_order_type' => '',
            'stockout_order_type_describle' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockoutOrderDetail::STOCKOUT_ORDER_TYPE_MAP',
                'replace' => 'stockout_order_type',
            ],
            'logistics_order_id'=>'',
            'stockout_order_source',
            'stockout_order_source_describle' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockoutOrderDetail::STOCKOUT_ORDER_SOURCE_MAP',
                'replace' => 'stockout_order_source',
            ],
            'order_create_time' => 'create_time',
            'expect_arrive_start_time' => '', //暂定
            'expect_arrive_end_time' => '', //暂定
            'customer_name',//
            'customer_id',
            'customer_contactor',
            'customer_contact',

        ],
        'slave' => [
            'sku_id',
            'upc_id',
            'sku_name',
            'import',
            'import_describle' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_StockoutOrderDetail::IMPORT_MAP',
                'replace' => 'import',
            ],
            'sku_net',
            'upc_unit',
            'upc_unit_text' => [
                'type' => Order_Statistics_Type::ARRAY,
                'array' => 'Order_Define_Sku::UPC_UNIT_MAP',
                'replace' => 'upc_unit',
            ],
            'sku_effect_type',
            'sku_effect_day',
            'order_amount',
            'distribute_amount',
            'pickup_amount',
            'effect_date',//暂定
            'cost_price',
            'cost_price_tax',
            'cost_total_price',
            'cost_total_price_tax',
            'send_price',
            'send_price_tax',
            'send_total_price',
            'send_total_price_tax',

        ],
        'sku' => [
            'category_1',
            'category_2',
            'category_3',
            'category_1_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 0,
                'replace' => 'sku_category_text',
            ],
            'sku_category_2_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 1,
                'replace' => 'sku_category_text',
            ],
            'sku_category_3_text' => [
                'type' => Order_Statistics_Type::FUNCTION_ARRAY,
                'function' => 'explode',
                'params' => [
                    ',',
                    self::REPLACE,
                ],
                'key' => 2,
                'replace' => 'sku_category_text',
            ],

        ],
    ];
}
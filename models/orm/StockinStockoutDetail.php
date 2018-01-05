<?php

/**
 * @property int $id
 * @property string $stockin_order_id
 * @property string $source_order_id
 * @property string $city_name
 * @property int $city_id
 * @property string $warehouse_name
 * @property int $warehouse_id
 * @property int $stockin_order_type
 * @property int $reserve_order_plan_time
 * @property string $reserve_order_plan_time_text
 * @property int $stockin_time
 * @property string $stockin_time_text
 * @property int $stockin_batch_id
 * @property int $stockin_order_status
 * @property int $stockin_order_status_text
 * @property int $client_id
 * @property int $client_name
 * @property string $client_contact
 * @property string $client_mobile
 * @property int $sku_id
 * @property string $upc_id
 * @property string $sku_name
 * @property int $sku_category_1
 * @property int $sku_category_2
 * @property int $sku_category_3
 * @property string $sku_category_1_text
 * @property string $sku_category_2_text
 * @property string $sku_category_3_text
 * @property int $sku_from_country
 * @property string $sku_from_country_text
 * @property string $sku_net
 * @property int $upc_unit
 * @property int $upc_unit_text
 * @property int $upc_unit_num
 * @property string $expire_date
 * @property int $stockin_order_real_amount
 * @property int $sku_price
 * @property int $sku_price_tax
 * @property int $stockin_order_sku_total_price
 * @property int $stockin_order_sku_total_price_tax
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinStockoutDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinStockoutDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinStockoutDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinStockoutDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinStockoutDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinStockoutDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockinStockoutDetail extends Order_Base_Orm
{

    public static $tableName = 'stockin_stockout_detail';
    public static $dbName = 'nwms_statistics';
    public static $clusterName = 'nwms_statistics_cluster';
}

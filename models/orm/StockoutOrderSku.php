<?php

/**
 * @property int $id
 * @property int $stockout_order_id
 * @property string $upc_id
 * @property int $sku_id
 * @property int $order_amount
 * @property int $distribute_amount
 * @property int $pickup_amount
 * @property string $sku_name
 * @property int $cost_price
 * @property int $cost_total_price
 * @property string $sku_net
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property int $send_price
 * @property int $send_total_price
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_StockoutOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockoutOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockoutOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockoutOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockoutOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockoutOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockoutOrderSku extends Wm_Orm_ActiveRecord
{

    public static $tableName = 'stockout_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

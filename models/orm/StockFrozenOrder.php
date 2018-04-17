<?php

/**
 * @property int $id
 * @property int $stock_frozen_order_id
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $order_status
 * @property int $origin_total_frozen_amount
 * @property int $current_total_frozen_amount
 * @property int $sku_amount
 * @property string $remark
 * @property int $create_type
 * @property int $creator
 * @property string $creator_name
 * @property int $close_user_id
 * @property string $close_user_name
 * @property int $close_time
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @property int $is_test
 * @method static Model_Orm_StockFrozenOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockFrozenOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockFrozenOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockFrozenOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockFrozenOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockFrozenOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockFrozenOrder extends Order_Base_Orm
{

    public static $tableName = 'stock_frozen_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

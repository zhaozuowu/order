<?php

/**
 * @property int $id
 * @property int $shift_order_id
 * @property int $warehouse_id
 * @property string $source_location
 * @property string $target_location
 * @property int $status
 * @property int $creator
 * @property string $sku_list
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @property int $is_test
 * @method static Model_Orm_ShiftOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_ShiftOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_ShiftOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_ShiftOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_ShiftOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_ShiftOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_ShiftOrder extends Wm_Orm_ActiveRecord
{

    public static $tableName = 'shift_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

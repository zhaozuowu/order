<?php

/**
 * @property int $id
 * @property int $shift_order_id
 * @property int $warehouse_id
 * @property int $sku_id
 * @property string $sku_name
 * @property string $upc_id
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property int $production_time
 * @property int $expiration_time
 * @property int $shift_amount
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @property int $is_test
 * @method static Model_Orm_ShiftOrderDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_ShiftOrderDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_ShiftOrderDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_ShiftOrderDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_ShiftOrderDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_ShiftOrderDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_ShiftOrderDetail extends Wm_Orm_ActiveRecord
{

    public static $tableName = 'shift_order_detail';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

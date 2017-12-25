<?php

/**
 * @property int $id
 * @property int $stockin_order_id
 * @property int $stockin_order_type
 * @property int $source_order_id
 * @property string $source_info
 * @property int $stockin_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $stockin_time
 * @property int $stockin_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $stockin_order_creator_id
 * @property string $stockin_order_creator_name
 * @property string $stockin_order_remark
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockinOrder extends Order_Base_Orm
{

    public static $tableName = 'stockin_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

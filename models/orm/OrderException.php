<?php

/**
 * @property int $id
 * @property int $exception_id
 * @property int $order_id
 * @property int $sku_id
 * @property string $sku_name
 * @property int $exception_type
 * @property int $exception_type_concrete
 * @property int $exception_level
 * @property string $exception_info
 * @property int $is_delete
 * @property int $version
 * @property int $update_time
 * @property int $create_time
 * @method static Model_Orm_OrderException findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_OrderException[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_OrderException[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_OrderException findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_OrderException[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_OrderException[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_OrderException extends Order_Base_Orm
{

    public static $tableName = 'order_exception';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

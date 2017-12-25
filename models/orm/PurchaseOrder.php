<?php

/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $stockin_order_id
 * @property int $nscm_purchase_order_id
 * @property int $purchase_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $purchase_order_plan_time
 * @property int $stockin_time
 * @property int $purchase_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $vendor_id
 * @property string $vendor_name
 * @property string $vendor_contactor
 * @property string $vendor_mobile
 * @property string $vendor_email
 * @property string $vendor_address
 * @property string $purchase_order_remark
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_PurchaseOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_PurchaseOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_PurchaseOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_PurchaseOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_PurchaseOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_PurchaseOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_PurchaseOrder extends Model_Orm_OrderBase
{

    public static $tableName = 'purchase_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

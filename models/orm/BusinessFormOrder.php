<?php

/**
 * @property int $id
 * @property int $business_form_order_id
 * @property int $business_form_order_type
 * @property int $business_form_order_price
 * @property string $business_form_order_remark
 * @property int $customer_id
 * @property string $customer_name
 * @property string $customer_contactor
 * @property string $customer_contact
 * @property string $customer_address
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_BusinessFormOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_BusinessFormOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_BusinessFormOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_BusinessFormOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_BusinessFormOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_BusinessFormOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_BusinessFormOrder extends Wm_Orm_ActiveRecord
{

    public static $tableName = 'business_form_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
    public static $arrDefaultColumns = [
        'id',
        'business_form_order_id',
        'business_form_order_type',
        'business_form_order_price',
        'business_form_order_remark',
        'customer_id',
        'status',
        'customer_name',
        'customer_contactor',
        'customer_contact',
        'customer_address',
        'create_time',
        'update_time',
        'is_delete'
    ];

    public static function getBusinessFormOrderListByConditions($arrConditions, $arrColumns = [], $intOffset = null, $intLimit = null)
    {
        if (empty($arrColumns)) {
            $arrColumns = self::$arrDefaultColumns;
        }
        return self::findRows($arrColumns, $arrConditions, ['create_time' => 'desc'], $intOffset, $intLimit);
    }


}

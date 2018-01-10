<?php

/**
 * @property int $id
 * @property int $business_form_order_id
 * @property int $sku_id
 * @property string $upc_id
 * @property string $sku_name
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property int $sku_net_gram
 * @property int $order_amount
 * @property int $stock_amount
 * @property int $distribute_amount
 * @property int $display_type
 * @property int $display_floor
 * @property int $cost_price
 * @property int $cost_total_price
 * @property int $send_price
 * @property int $send_total_price
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_BusinessFormOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_BusinessFormOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_BusinessFormOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_BusinessFormOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_BusinessFormOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_BusinessFormOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_BusinessFormOrderSku extends Order_Base_Orm
{

    public static $tableName = 'business_form_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 获取业态订单sku信息
     * @param $arrConditions
     * @param array $arrColumns
     * @param null $intOffset
     * @param null $intLimit
     * @return array
     */
    public static function getBusSkuListByConditions($arrConditions, $arrColumns = [], $intOffset = null, $intLimit = null)
    {
        if (empty($arrColumns)) {
            $arrColumns = self::getAllColumns();
        }
        return self::findRows($arrColumns, $arrConditions, ['create_time' => 'desc'], $intOffset, $intLimit);
    }

}

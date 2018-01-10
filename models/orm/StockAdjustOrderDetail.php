<?php

/**
 * @property int $id
 * @property int $stock_adjust_order_id
 * @property int $warehouse_id
 * @property int $adjust_type
 * @property int $sku_id
 * @property string $sku_name
 * @property int $adjust_amount
 * @property string $upc_id
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property int $unit_price
 * @property int $unit_price_tax
 * @property int $production_time
 * @property int $expire_time
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockAdjustOrderDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockAdjustOrderDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockAdjustOrderDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockAdjustOrderDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockAdjustOrderDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockAdjustOrderDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockAdjustOrderDetail extends Order_Base_Orm
{

    public static $tableName = 'stock_adjust_order_detail';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

<?php

/**
 * @property int $id
 * @property int $stockin_order_id
 * @property int $sku_id
 * @property string $upc_id
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property string $sku_name
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property string $sku_net_gram
 * @property int $sku_price
 * @property int $sku_price_tax
 * @property int $stockin_order_sku_total_price
 * @property int $stockin_order_sku_total_price_tax
 * @property int $reserve_order_sku_plan_amount
 * @property int $stockin_order_sku_real_amount
 * @property string $stockin_order_sku_extra_info
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockinOrderSku extends Order_Base_Orm
{

    public static $tableName = 'stockin_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
}

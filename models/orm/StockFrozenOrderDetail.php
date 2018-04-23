<?php

/**
 * @property int $id
 * @property int $stock_frozen_order_id
 * @property int $warehouse_id
 * @property int $sku_id
 * @property string $upc_id
 * @property string $sku_name
 * @property string $storage_location_id
 * @property int $origin_frozen_amount
 * @property int $current_frozen_amount
 * @property int $is_defective
 * @property int $production_time
 * @property int $expire_time
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @property int $is_test
 * @method static Model_Orm_StockFrozenOrderDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockFrozenOrderDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockFrozenOrderDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockFrozenOrderDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockFrozenOrderDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockFrozenOrderDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockFrozenOrderDetail extends Order_Base_Orm
{

    public static $tableName = 'stock_frozen_order_detail';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 根据SKU_ID查询所有冻结单ID
     * @param $intSkuId
     * @return array
     */
    public static function getOrderIdsBySkuId($intSkuId) {
        $arrWhere = [
            'is_delete'             => Order_Define_Const::NOT_DELETE,
            'sku_id' => $intSkuId,
        ];

        $arrRet = Model_Orm_StockFrozenOrderDetail::findRows(['stock_frozen_order_id'], $arrWhere);
        return $arrRet;
    }
}

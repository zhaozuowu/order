<?php
/**
 * @property int $id
 * @property int $stockout_order_id
 * @property int $pickup_order_id
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockoutPickupOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockoutPickupOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockoutPickupOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockoutPickupOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockoutPickupOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockoutPickupOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockoutPickupOrder extends Order_Base_Orm
{
    public static $tableName = 'stockout_pickup_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * get pickup order ids
     * @param $intStockoutOrderId
     * @return array
     */
    public static function getPickupOrderIdsByStockoutOrderId($intStockoutOrderId)
    {
        $ret = [];
        $arrFields = [
            'pickup_order_id'
        ];
        $arrConds = [];
        $arrConds['stockout_order_id'] = intval($intStockoutOrderId);
        $arrConds['is_delete'] = Order_Define_Const::NOT_DELETE;
        $arrOrderBy = [
            'id' => 'desc',
        ];
        $retDB = self::findRows($arrFields, $arrConds, $arrOrderBy);
        foreach ((array)$retDB as $arrRelationOfPickupAndStockout) {
            $ret[] = $arrRelationOfPickupAndStockout['pickup_order_id'];
        }
        return $ret;
    }
}

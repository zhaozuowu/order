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

    /**
     * create stock in order
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intSourceOrderId
     * @param string $strSourceInfo
     * @param int $intStockinOrderStatus
     * @param int $intWarehouseId
     * @param $strWarehouseName
     * @param int $intStockinTime
     * @param int $intStockinOrderPlanAmount
     * @param int $intStockinOrderReadAmount
     * @param int $intStockinOrderCreatorId
     * @param string $strStockinOrderCreatorName
     * @param string $strStockinOrderRemark
     * @return int
     */
    public static function createStockinOrder(
        $intStockinOrderId,
        $intStockinOrderType,
        $intSourceOrderId,
        $strSourceInfo,
        $intStockinOrderStatus,
        $intWarehouseId,
        $strWarehouseName,
        $intStockinTime,
        $intStockinOrderPlanAmount,
        $intStockinOrderReadAmount,
        $intStockinOrderCreatorId,
        $strStockinOrderCreatorName,
        $strStockinOrderRemark
    )
    {
        $arrRow = [
            'stockin_order_id' => intval($intStockinOrderId),
            'stockin_order_type' => intval($intStockinOrderType),
            'source_order_id' => intval($intSourceOrderId),
            'source_info' => strval($strSourceInfo),
            'stockin_order_status' => intval($intStockinOrderStatus),
            'warehouse_id' => intval($intWarehouseId),
            'warehouse_name' => intval($strWarehouseName),
            'stockin_time' => $intStockinTime,
            'stockin_order_plan_amount' => $intStockinOrderPlanAmount,
            'stockin_order_real_amount' => $intStockinOrderReadAmount,
            'stockin_order_creator_id' => $intStockinOrderCreatorId,
            'stockin_order_creator_name' => $strStockinOrderCreatorName,
            'stockin_order_remark' => $strStockinOrderRemark,
        ];
        return self::insert($arrRow);
    }
}

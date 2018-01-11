<?php

/**
 * @property int $id
 * @property int $stock_adjust_order_id
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $total_adjust_amount
 * @property int $adjust_type
 * @property int $adjust_amount_type
 * @property string $remark
 * @property int $creator
 * @property string $creator_name
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockAdjustOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockAdjustOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockAdjustOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockAdjustOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockAdjustOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockAdjustOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockAdjustOrder extends Order_Base_Orm
{

    public static $tableName = 'stock_adjust_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 根据调整单id获取调整单
     * @param $stock_adjust_order_id
     * @return Model_Orm_StockAdjustOrder
     */
    public static function getByOrderId($stock_adjust_order_id)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $stock_adjust_order_id);

        if(empty($stock_adjust_order_id)) {
            Bd_Log::warning('调整单id不正确', Order_Error_Code::PARAMS_ERROR, $stock_adjust_order_id);
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        $arrConditions = [
            'is_delete'             => Order_Define_Const::NOT_DELETE,
            'stock_adjust_order_id' => $stock_adjust_order_id,
        ];

        // 获取所有字段
        $arrColumns = self::getAllColumns();

        $arrRet = Model_Orm_StockAdjustOrder::findRow($arrColumns, $arrConditions);

        Bd_Log::debug(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        return $arrRet;
    }
}

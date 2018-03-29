<?php

/**
 * @property int $id
 * @property int $stockin_order_id
 * @property int $stockin_order_type
 * @property int $source_order_id
 * @property int $source_supplier_id
 * @property string $source_info
 * @property int $stockin_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $stockin_time
 * @property int $reserve_order_plan_time
 * @property int $stockin_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $stockin_order_creator_id
 * @property string $stockin_order_creator_name
 * @property string $stockin_order_remark
 * @property int $stockin_order_total_price
 * @property int $stockin_order_total_price_tax
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
     * @param int $intStockinBatchId
     * @param int $intSourceSupplierId
     * @param string $strSourceInfo
     * @param int $intStockinOrderStatus
     * @param int $intCityId
     * @param string $strCityName
     * @param int $intWarehouseId
     * @param string $strWarehouseName
     * @param int $intStockinTime
     * @param int $intReserveOrderPlanTime
     * @param int $intStockinOrderPlanAmount
     * @param int $intStockinOrderRealAmount
     * @param int $intStockinOrderCreatorId
     * @param string $strStockinOrderCreatorName
     * @param string $strStockinOrderRemark
     * @param int $intStockinOrderTotalPrice
     * @param  int $intStockinOrderTotalPriceTax
     * @return int
     */
    public static function createStockinOrder(
        $intStockinOrderId,
        $intStockinOrderType,
        $intSourceOrderId,
        $intStockinBatchId,
        $intSourceSupplierId,
        $strSourceInfo,
        $intStockinOrderStatus,
        $intCityId,
        $strCityName,
        $intWarehouseId,
        $strWarehouseName,
        $intStockinTime,
        $intReserveOrderPlanTime,
        $intStockinOrderPlanAmount,
        $intStockinOrderRealAmount,
        $intStockinOrderCreatorId,
        $strStockinOrderCreatorName,
        $strStockinOrderRemark,
        $intStockinOrderTotalPrice,
        $intStockinOrderTotalPriceTax
    )
    {
        $arrRow = [
            'stockin_order_id' => intval($intStockinOrderId),
            'stockin_order_type' => intval($intStockinOrderType),
            'source_order_id' => intval($intSourceOrderId),
            'stockin_batch_id' => intval($intStockinBatchId),
            'source_supplier_id' => intval($intSourceSupplierId),
            'source_info' => strval($strSourceInfo),
            'stockin_order_status' => intval($intStockinOrderStatus),
            'city_id' => intval($intCityId),
            'city_name' => strval($strCityName),
            'warehouse_id' => intval($intWarehouseId),
            'warehouse_name' => strval($strWarehouseName),
            'stockin_time' => $intStockinTime,
            'reserve_order_plan_time' => $intReserveOrderPlanTime,
            'stockin_order_plan_amount' => $intStockinOrderPlanAmount,
            'stockin_order_real_amount' => $intStockinOrderRealAmount,
            'stockin_order_creator_id' => $intStockinOrderCreatorId,
            'stockin_order_creator_name' => $strStockinOrderCreatorName,
            'stockin_order_remark' => $strStockinOrderRemark,
            'stockin_order_total_price' => $intStockinOrderTotalPrice,
            'stockin_order_total_price_tax' => $intStockinOrderTotalPriceTax,
        ];
        return self::insert($arrRow);
    }

    /**
     * 获取入库单列表（分页）
     *
     * @param $arrStockinOrderType
     * @param $intStockinOrderId
     * @param $arrWarehouseId
     * @param $intSourceSupplierId
     * @param $arrSourceOrderIdInfo
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return mixed
     */
    public static function getStockinOrderList(
        $arrStockinOrderType,
        $intStockinOrderId,
        $arrWarehouseId,
        $intSourceSupplierId,
        $arrSourceOrderIdInfo,
        $arrCreateTime,
        $arrOrderPlanTime,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        // 拼装查询条件
        if (!empty($intStockinOrderId)) {
            $arrCondition['stockin_order_id'] = $intStockinOrderId;
        }

        if (!empty($arrSourceOrderIdInfo)) {
            $arrCondition['source_order_id'] = $arrSourceOrderIdInfo['source_order_id'];
            // 入库单类型
            $arrCondition['stockin_order_type'] = $arrSourceOrderIdInfo['source_order_type'];
        }

        // 必填入库单参数类型
        if (!empty($arrStockinOrderType)) {
            $arrCondition['stockin_order_type'] = [
                'in',
                $arrStockinOrderType];
        }

        if (!empty($arrWarehouseId)) {
            $arrCondition['warehouse_id'] = [
                'in',
                $arrWarehouseId];
        }

        if (!empty($intSourceSupplierId)) {
            $arrCondition['source_supplier_id'] = $intSourceSupplierId;
        }

        if (!empty($arrCreateTime['start'])
            && !empty($arrCreateTime['end'])) {
            $arrCondition['create_time'] = [
                'between',
                $arrCreateTime['start'],
                $arrCreateTime['end']
            ];
        }

        if (!empty($arrOrderPlanTime['start'])
            && !empty($arrOrderPlanTime['end'])) {
            $arrCondition['reserve_order_plan_time'] = [
                'between',
                $arrOrderPlanTime['start'],
                $arrOrderPlanTime['end']
            ];
        }

        if (!empty($arrStockinTime['start'])
            && !empty($arrStockinTime['end'])) {
            $arrCondition['stockin_time'] = [
                'between',
                $arrStockinTime['start'],
                $arrStockinTime['end'],
            ];
        }

        // 只查询未软删除的
        $arrCondition['is_delete'] = Order_Define_Const::NOT_DELETE;

        // 排序条件
        $orderBy = ['id' => 'desc'];

        // 分页条件
        $offset = (intval($intPageNum) - 1) * intval($intPageSize);
        $limitCount = intval($intPageSize);

        // 查找满足条件的所有列数据
        $arrCols = self::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = self::findRowsAndTotalCount(
            $arrCols,
            $arrCondition,
            $orderBy,
            $offset,
            $limitCount);

        $arrResult['total'] = $arrRowsAndTotal['total'];
        $arrResult['list'] = $arrRowsAndTotal['rows'];

        return $arrResult;
    }

    /**
     * 查询入库单详情
     *
     * @param $intStockinOrderId
     * @return mixed
     */
    public static function getStockinOrderInfoByStockinOrderId($intStockinOrderId)
    {
        // 只查询未软删除的
        $arrCondition = [
            'is_delete' => Order_Define_Const::NOT_DELETE,
            'stockin_order_id' => $intStockinOrderId,
        ];

        // 查找该行所有数据
        $arrCols = self::getAllColumns();

        // 查找满足条件的所有行数据
        $arrResult = self::findRow($arrCols, $arrCondition);

        return $arrResult;
    }

    /**
     * 对输入的入库单类型进行校验，为空或者不符合返回false
     *
     * @param $arrStockinOrderType
     * @return bool
     */
    public static function isStockinOrderTypeCorrect($arrStockinOrderType)
    {
        // 如果为空返回为错误
        if (empty($arrStockinOrderType)) {
            return false;
        }

        foreach ($arrStockinOrderType as $intType) {
            if (true !== Order_Define_StockinOrder::STOCKIN_ORDER_TYPES[$intType]) {
                return false;
            }
        }

        return true;
    }

}

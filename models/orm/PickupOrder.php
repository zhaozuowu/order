<?php
/**
 * @property int $id
 * @property int $pickup_order_id
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $pickup_order_status
 * @property int $pickup_order_type
 * @property int $pickup_order_is_print
 * @property int $stockout_order_amount
 * @property int $sku_distribute_amount
 * @property int $sku_kind_amount
 * @property int $sku_pickup_amount
 * @property string $creator
 * @property string $remark
 * @property int $create_time
 * @property int $update_time
 * @property string $update_operator
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_PickupOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_PickupOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_PickupOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_PickupOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_PickupOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_PickupOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_PickupOrder extends Order_Base_Orm
{
    public static $tableName = 'pickup_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';


    /**
     * get pick up order
     * @param $arrWarehouseIds
     * @param $intCreateStartTime
     * @param $intCreateEndTime
     * @param $intPageSize
     * @param int $intPageNum
     * @param int $intPickupOrderStatus
     * @param array $arrPickupOrderIds
     * @param int $intPickupOrderIsPrint
     * @param int $intUpdateStartTime
     * @param int $intUpdateEndTime
     * @return array
     */
    public static function getPickupOrderList($arrWarehouseIds,
                                              $intCreateStartTime,
                                              $intCreateEndTime,
                                              $intPageSize,
                                              $intPageNum = 1,
                                              $intPickupOrderStatus = 0,
                                              $arrPickupOrderIds = [],
                                              $intPickupOrderIsPrint = 0,
                                              $intUpdateStartTime = 0,
                                              $intUpdateEndTime = 0)
    {
        $arrCondition = [];
        $arrCols = self::getAllColumns();
        $arrCondition['create_time'] = [
            'between',
            $intCreateStartTime,
            $intCreateEndTime,
        ];
        if (!empty($intUpdateStartTime) && !empty($intUpdateEndTime)) {
            $arrCondition['update_time'] = [
                'between',
                $intUpdateStartTime,
                $intUpdateEndTime,
            ];
        }
        if (!empty($arrWarehouseIds)) {
            $arrCondition['warehouse_id'] = ['in', $arrWarehouseIds];
        }
        if (!empty($intPickupOrderStatus)) {
            $arrCondition['pickup_order_status'] = intval($intPickupOrderStatus);
        }
        if (!empty($arrPickupOrderIds)) {
            $arrCondition['pickup_order_id'] = [
                'in',
                $arrPickupOrderIds,
            ];
        }
        if (!empty($intPickupOrderIsPrint) && in_array($intPickupOrderIsPrint,
                Order_Define_PickupOrder::PICKUP_ORDER_PRINT_STATUS)) {
            $arrCondition['pickup_order_is_print'] = $intPickupOrderIsPrint;
        }
        $arrCondition['is_delete'] = Order_Define_Const::NOT_DELETE;
        $arrOrderBy= ['id' => 'desc'];
        $intOffset = (intval($intPageNum) - 1) * intval($intPageSize);
        $intLimit = empty($intPageSize) ? null : intval($intPageSize);

        $arrRowsAndTotal = self::findRowsAndTotalCount(
            $arrCols,
            $arrCondition,
            $arrOrderBy,
            $intOffset,
            $intLimit);
        return $arrRowsAndTotal;
    }

    /**
     * @param array $arrWarehouseIds
     * @param array $arrStatus
     * @param array $arrType
     * @return int
     */
    public static function getCountByWarehouseIdStatusType(array $arrWarehouseIds, array $arrStatus, array $arrType = [])
    {
        if (empty($arrWarehouseIds) || empty($arrStatus)) {
            return 0;
        }
        $arrCond = [
            'warehouse_id' => ['in', $arrWarehouseIds],
            'pickup_order_status' => ['in', $arrStatus],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        if (!empty($arrType)) {
            $arrCond['pickup_order_type'] = ['in', $arrType];
        }
        return self::count($arrCond);
    }

    /**
     * @param array $arrOrderIds
     * @return array
     */
    public static function getOrderInfoByOrderIds(array $arrOrderIds)
    {
        if (empty($arrOrderIds)) {
            return [];
        }
        $arrCond = [
            'pickup_order_id' => ['in', $arrOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return self::findRows(self::getAllColumns(), $arrCond);
    }

    /**
     * 获取拣货订单对象
     * @param int   $intPickupOrderId
     * @param bool  $boolFormatArr
     * @return Model_Orm_PickupOrder|array
     */
    public static function getPickupOrderInfo($intPickupOrderId, $boolFormatArr = false)
    {
        $arrCondition = [
            'pickup_order_id' => $intPickupOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $objPickupOrderInfo = self::findOne($arrCondition);
        if ($boolFormatArr) {
            return $objPickupOrderInfo->toArray();
        }
        return $objPickupOrderInfo;
    }

    /**
     * 通过拣货单id更新拣货订单信息
     * @param $intPickupOrderId
     * @param $arrOrderUpdateFields
     * @throws Order_BusinessError
     */
    public static function updatePickupOrderInfoById($intPickupOrderId, $arrOrderUpdateFields)
    {
        $objPickupOrderInfo = self::getPickupOrderInfo($intPickupOrderId);
        if (empty($objPickupOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }
        $objPickupOrderInfo->update($arrOrderUpdateFields);
    }
}

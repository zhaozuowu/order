<?php

/**
 * @property int $id
 * @property int $reserve_order_id
 * @property int $stockin_order_id
 * @property int $purchase_order_id
 * @property int $reserve_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $reserve_order_plan_time
 * @property int $stockin_time
 * @property int $reserve_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $vendor_id
 * @property string $vendor_name
 * @property string $vendor_contactor
 * @property string $vendor_mobile
 * @property string $vendor_email
 * @property string $vendor_address
 * @property string $reserve_order_remark
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_ReserveOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_ReserveOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_ReserveOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_ReserveOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_ReserveOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_ReserveOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_ReserveOrder extends Order_Base_Orm
{
    public static $tableName = 'reserve_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 查询采购订单列表
     *
     * @param $arrPurchaseOrderStatus
     * @param $arrWarehouseId
     * @param $intPurchaseOrderId
     * @param $intVendorId
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     */
    public static function getReserveOrderList(
        $arrPurchaseOrderStatus,
        $arrWarehouseId,
        $intPurchaseOrderId,
        $intVendorId,
        $arrCreateTime,
        $arrOrderPlanTime,
        $arrStockinTime,
        $intPageNum,
        $intPageSize
    )
    {
        // 拼装查询条件
        if (!empty($arrPurchaseOrderStatus)) {
            $arrCondition['purchase_order_status'] = [
                'in',
                $arrPurchaseOrderStatus];
        }

        if (!empty($arrWarehouseId)) {
            $arrCondition['warehouse_id'] = [
                'in',
                $arrWarehouseId];
        }

        if (!empty($intPurchaseOrderId)) {
            $arrCondition['purchase_order_id'] = $intPurchaseOrderId;
        }

        if (!empty($intVendorId)) {
            $arrCondition['vendor_id'] = $intVendorId;
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
            $arrCondition['purchase_order_plan_time'] = [
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

        // 查找满足条件的所有行数据
        $arrRows = Model_Orm_ReserveOrder::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = Model_Orm_ReserveOrder::findRowsAndTotalCount(
            $arrRows,
            $arrCondition,
            $orderBy,
            $offset,
            $limitCount);

        $arrResult['total'] = $arrRowsAndTotal['total'];
        $arrResult['list'] = $arrRowsAndTotal['rows'];

        return $arrResult;
    }

    /**
     * 获取采购单状态统计，只查询未软删除的
     *
     * @return array
     */
    public static function getReserveOrderStatistics()
    {
        $arrCond = ['is_delete' => Order_Define_Const::NOT_DELETE];
        $arrResult = Model_Orm_ReserveOrder::find($arrCond)
            ->select(['purchase_order_status', 'count(*) as purchase_order_status_count'])
            ->groupBy(['purchase_order_status'])
            ->orderBy(['purchase_order_status' => 'desc'])
            ->rows();

        return $arrResult;
    }

    /**
     * update status
     * @param $intStatus
     * @return bool
     */
    public function updateStatus($intStatus)
    {
        $this->reserve_order_status = $intStatus;
        return $this->update();
    }

    /**
     * get reserve info by reserve order id
     * @param $intPurchaseOrderId
     * @return Model_Orm_ReserveOrder
     */
    public static function getReserveInfoByPurchaseOrderId($intPurchaseOrderId)
    {
        $arrCondition = [
            'reserve_order_id' => $intPurchaseOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return self::findOne($arrCondition);
    }

    /**
     * create reserve order
     * @param $intReserveOrderId
     * @param $intPurchaseOrderId
     * @param $intWarehouseId
     * @param $strWarehouseName
     * @param $intReserveOrderPlanTime
     * @param $intReserveOrderPlanAmount
     * @param $intVendorId
     * @param $strVendorName
     * @param $strVendorContactor
     * @param $strVendorMobile
     * @param $strVendorEmail
     * @param $strVendorAddress
     * @param $strReserveOrderRemark
     * @return int
     */
    public static function createReserveOrder($intReserveOrderId, $intPurchaseOrderId,
        $intWarehouseId, $strWarehouseName, $intReserveOrderPlanTime,$intReserveOrderPlanAmount,
        $intVendorId, $strVendorName, $strVendorContactor,$strVendorMobile, $strVendorEmail,
        $strVendorAddress, $strReserveOrderRemark
    )
    {
        $arrDb = [
            'reserve_order_id' => $intReserveOrderId,
            'stockin_order_id' => 0,
            'purchase_order_id' => $intPurchaseOrderId,
            'reserve_order_status' => Order_Define_ReserveOrder::STATUS_STOCKING,
            'warehouse_id' => $intWarehouseId,
            'warehouse_name' => $strWarehouseName,
            'reserve_order_plan_time' => $intReserveOrderPlanTime,
            'stockin_time' => 0,
            'reserve_order_plan_amount' => $intReserveOrderPlanAmount,
            'stockin_order_real_amount' => 0,
            'vendor_id' => $intVendorId,
            'vendor_name' => $strVendorName,
            'vendor_contactor' => $strVendorContactor,
            'vendor_mobile' => $strVendorMobile,
            'vendor_email' => $strVendorEmail,
            'vendor_address' => $strVendorAddress,
            'reserve_order_remark' => $strReserveOrderRemark,
        ];
        return self::insert($arrDb);
    }
}

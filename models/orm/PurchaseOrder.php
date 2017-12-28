<?php

/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $stockin_order_id
 * @property int $nscm_purchase_order_id
 * @property int $purchase_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $purchase_order_plan_time
 * @property int $stockin_time
 * @property int $purchase_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $vendor_id
 * @property string $vendor_name
 * @property string $vendor_contactor
 * @property string $vendor_mobile
 * @property string $vendor_email
 * @property string $vendor_address
 * @property string $purchase_order_remark
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_PurchaseOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_PurchaseOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_PurchaseOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_PurchaseOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_PurchaseOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_PurchaseOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_PurchaseOrder extends Order_Base_Orm
{
    public static $tableName = 'purchase_order';
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
    public static function getPurchaseOrderList(
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
        $arrRows = Model_Orm_PurchaseOrder::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = Model_Orm_PurchaseOrder::findRowsAndTotalCount(
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
    public static function getPurchaseOrderStatistics()
    {
        $arrCond = ['is_delete' => Order_Define_Const::NOT_DELETE];
        $arrResult = Model_Orm_PurchaseOrder::find($arrCond)
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
        $this->purchase_order_status = $intStatus;
        return $this->update();
    }

    /**
     * get purchase info by nscm purchase order id
     * @param $intNscmPurchaseOrderId
     * @return Model_Orm_PurchaseOrder
     */
    public static function getPurchaseInfoByNscmPurchaseOrderId($intNscmPurchaseOrderId)
    {
        $arrCondition = [
            'nscm_purchase_order_id' => $intNscmPurchaseOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return self::findOne($arrCondition);
    }

    /**
     * create purchase order
     * @param $intPurchaseOrderId
     * @param $intNscmPurchaseOrderId
     * @param $intWarehouseId
     * @param $strWarehouseName
     * @param $intPurchaseOrderPlanTime
     * @param $intPurchaseOrderPlanAmount
     * @param $intVendorId
     * @param $strVendorName
     * @param $strVendorContactor
     * @param $strVendorMobile
     * @param $strVendorEmail
     * @param $strVendorAddress
     * @param $strPurchaseOrderRemark
     * @return int
     */
    public static function createPurchaseOrder($intPurchaseOrderId, $intNscmPurchaseOrderId,
                                               $intWarehouseId, $strWarehouseName, $intPurchaseOrderPlanTime, $intPurchaseOrderPlanAmount,
                                               $intVendorId, $strVendorName, $strVendorContactor, $strVendorMobile, $strVendorEmail,
                                               $strVendorAddress, $strPurchaseOrderRemark
    )
    {
        $arrDb = [
            'purchase_order_id' => $intPurchaseOrderId,
            'stockin_order_id' => 0,
            'nscm_purchase_order_id' => $intNscmPurchaseOrderId,
            'purchase_order_status' => Order_Define_PurchaseOrder::STATUS_STOCKING,
            'warehouse_id' => $intWarehouseId,
            'warehouse_name' => $strWarehouseName,
            'purchase_order_plan_time' => $intPurchaseOrderPlanTime,
            'stockin_time' => 0,
            'purchase_order_plan_amount' => $intPurchaseOrderPlanAmount,
            'stockin_order_real_amount' => 0,
            'vendor_id' => $intVendorId,
            'vendor_name' => $strVendorName,
            'vendor_contactor' => $strVendorContactor,
            'vendor_mobile' => $strVendorMobile,
            'vendor_email' => $strVendorEmail,
            'vendor_address' => $strVendorAddress,
            'purchase_order_remark' => $strPurchaseOrderRemark,
        ];
        return self::insert($arrDb);
    }
}

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
        // 返回结果数据
        $rows = [
            'vendor_name',
            'purchase_order_id',
            'stockin_order_id',
            'purchase_order_status',
            'warehouse_name',
            'purchase_order_plan_time',
            'stockin_time',
            'purchase_order_plan_amount',
            'stockin_order_real_amount',
            'purchase_order_remark',
        ];

        // 拼装查询条件
        $condition = [];

        if (!empty($arrPurchaseOrderStatus)) {
            $condition['purchase_order_status'] = [
                'in',
                $arrPurchaseOrderStatus];
        }

        if (!empty($arrWarehouseId) && !empty($arrWarehouseId[0])) {
            $condition['warehouse_id'] = [
                'in',
                $arrWarehouseId];
        }

        if (!empty($intPurchaseOrderId)) {
            $condition['purchase_order_id'] = $intPurchaseOrderId;
        }

        if (!empty($intVendorId)) {
            $condition['vendor_id'] = $intVendorId;
        }

        if (!empty($arrCreateTime)) {
            $condition['create_time'] = [
                'between',
                $arrCreateTime['start'],
                $arrCreateTime['end']
            ];
        }

        if (!empty($arrOrderPlanTime)) {
            $condition['purchase_order_plan_time'] = [
                'between',
                $arrOrderPlanTime['start'],
                $arrOrderPlanTime['end']
            ];
        }

        if (!empty($arrStockinTime)) {
            $condition['stockin_time'] = [
                'between',
                $arrStockinTime['start'],
                $arrStockinTime['end'],
            ];
        }

        //查询未软删除的
        $condition['is_delete'] = Order_Define_Const::NOT_DELETE;

        if((0 >= $intPageNum) || empty($intPageNum)){
            $intPageNum = 1;
        }

        // 分页条件
        $offset = (intval($intPageNum ) - 1) * intval($intPageSize);
        $limitCount = intval($intPageSize);


        return Model_Orm_PurchaseOrder::findRows($rows, $condition, [],  $offset, $limitCount);
    }
}

<?php

/**
 * @property int $id
 * @property int $pickup_order_id
 * @property int $stockout_order_id
 * @property string $upc_id
 * @property int $sku_id
 * @property string $sku_name
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property int $order_amount
 * @property int $distribute_amount
 * @property int $pickup_amount
 * @property string $pickup_extra_info
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_PickupOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_PickupOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_PickupOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_PickupOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_PickupOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_PickupOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_PickupOrderSku extends Order_Base_Orm
{

    public static $tableName = 'pickup_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * @param array $arrOrderIds
     * @return array
     */
    public static function getOrderSkuInfoByOrderIds(array $arrOrderIds)
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
     * @param $intPickupOrderId
     * @return array
     */
    public static function getSkuListByPickupOrderId($intPickupOrderId)
    {
        $arrConds = [
            'pickup_order_id' => $intPickupOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return self::findRows(self::getAllColumns(), $arrConds);
    }

    /**
     * 更新拣货单sku信息
     * @param $arrSkuUpdateFields
     * @param $arrSkuUpdateCondition
     */
    public static function updatePickupInfo($arrSkuUpdateFields, $arrSkuUpdateCondition)
    {
        foreach ($arrSkuUpdateFields as $key => $arrSkuUpdateField) {
            $objPickupOrderSkuInfo = Model_Orm_StockoutOrderSku::findOne($arrSkuUpdateCondition[$key]);
            if (!empty($objPickupOrderSkuInfo)) {
                $objPickupOrderSkuInfo->pickup_amount = $arrSkuUpdateField['pickup_amount'];
                $objPickupOrderSkuInfo->pickup_extra_info = $arrSkuUpdateField['pickup_extra_info'];
            }
        }
    }
}

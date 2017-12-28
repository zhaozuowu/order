<?php

/**
 * @property int $id
 * @property int $reserve_order_id
 * @property int $sku_id
 * @property string $upc_id
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property string $sku_name
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property string $sku_net_gram
 * @property int $sku_price
 * @property int $sku_price_tax
 * @property int $reserve_order_sku_total_price
 * @property int $reserve_order_sku_total_price_tax
 * @property int $reserve_order_sku_plan_amount
 * @property int $stockin_order_sku_real_amount
 * @property string $stockin_order_sku_extra_info
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_ReserveOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_ReserveOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_ReserveOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_ReserveOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_ReserveOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_ReserveOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_ReserveOrderSku extends Order_Base_Orm
{

    public static $tableName = 'reserve_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * create reserve order sku
     * @param $arrReserveOrderSkus
     * @param $intReserveOrderId
     * @return int
     */
    public static function createReserveOrderSku($arrReserveOrderSkus, $intReserveOrderId)
    {
        $arrDbReserveOrderSkus = [];
        foreach ($arrReserveOrderSkus as $arrInputRow) {
            $arrRow = [
                'reserve_order_id' => $intReserveOrderId,
                'sku_id' => $arrInputRow['sku_id'],
                'upc_id' => $arrInputRow['upc_id'],
                'upc_unit' => $arrInputRow['upc_unit'],
                'upc_unit_num' => $arrInputRow['upc_unit_num'],
                'sku_name' => $arrInputRow['sku_name'],
                'sku_net' => $arrInputRow['sku_net'],
                'sku_net_unit' => $arrInputRow['sku_net_unit'],
                'sku_net_gram' => $arrInputRow['sku_net_gram'],
                'sku_price' => $arrInputRow['sku_price'],
                'sku_price_tax' => $arrInputRow['sku_price_tax'],
                'reserve_order_sku_total_price' => $arrInputRow['reserve_order_sku_total_price'],
                'reserve_order_sku_total_price_tax' => $arrInputRow['reserve_order_sku_total_price_tax'],
                'reserve_order_sku_plan_amount' => $arrInputRow['reserve_order_sku_plan_amount'],
                'stockin_order_sku_real_amount' => 0,
                'stockin_order_sku_extra_info' => '',
            ];
            $arrDbReserveOrderSkus[] = $arrRow;
        }
        return self::batchInsert($arrDbReserveOrderSkus);
    }
}

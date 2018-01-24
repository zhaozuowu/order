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
 * @property int $sku_effect_type
 * @property int $sku_effect_day
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
                'sku_tax_rate' => $arrInputRow['sku_tax_rate'],
                'sku_effect_type' => $arrInputRow['sku_effect_type'],
                'sku_effect_day' => $arrInputRow['sku_effect_day'],
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

    /**
     * get reserve order skus by reserve order id
     * @param int $intReserveOrderId
     * @param int $intPageSize
     * @param int $intPageNum
     * @return array
     */
    public static function getReserveOrderSkusByReserveOrderId($intReserveOrderId, $intPageSize = 0, $intPageNum = 1)
    {
        $arrConds = [
            'reserve_order_id' => $intReserveOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrOrderBy = [
            'sku_id' => 'desc',
        ];
        $offset = ($intPageNum - 1) * $intPageSize;
        $limit = empty($intPageSize) ? null : $intPageSize;
        $arrResult = self::findRowsAndTotalCount(self::getAllColumns(), $arrConds, $arrOrderBy, $offset, $limit);
        return $arrResult;
    }

    /**
     * 查询指定订单的商品列表
     *
     * @param $intReserveOrderId
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     */
    public static function getReserveOrderSkuList(
        $intReserveOrderId,
        $intPageNum,
        $intPageSize)
    {
        $arrResult = [
            'total' => '0',
            'list' => [],
        ];

        if (empty($intReserveOrderId)) {
            return $arrResult;
        }

        // 只查询未软删除的
        $arrCondition = [
            'reserve_order_id' => $intReserveOrderId,
            'is_delete'  => Order_Define_Const::NOT_DELETE,
        ];

        // 排序条件
        $orderBy = ['sku_id' => 'asc'];

        // 分页条件
        $offset = (intval($intPageNum) - 1) * intval($intPageSize);

        // 查询所有情况的条件page_size = 0
        $limitCount = empty($intPageSize) ? null : $intPageSize;

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
     * sync stockin sku info
     * @param $intStockinOrderSkuRealAmount
     * @param $strStockinOrderSkuExtraInfo
     * @return bool
     */
    public function syncStockinSkuInfo($intStockinOrderSkuRealAmount, $strStockinOrderSkuExtraInfo)
    {
        $this->stockin_order_sku_real_amount = $intStockinOrderSkuRealAmount;
        $this->stockin_order_sku_extra_info = $strStockinOrderSkuExtraInfo;
        return $this->update();
    }

    /**
     * find all stock in sku
     * @param $intStockinOrderId
     * @return Model_Orm_ReserveOrderSku[]
     */
    public static function findAllStockinSku($intStockinOrderId)
    {
        return static::findAll(['reserve_order_id' => $intStockinOrderId, 'is_delete' => Order_Define_Const::NOT_DELETE]);
    }
}

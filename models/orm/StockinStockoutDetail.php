<?php

/**
 * @property int $id
 * @property string $stockin_order_id
 * @property string $source_order_id
 * @property string $city_name
 * @property int $city_id
 * @property string $warehouse_name
 * @property int $warehouse_id
 * @property int $stockin_order_type
 * @property int $reserve_order_plan_time
 * @property string $reserve_order_plan_time_text
 * @property int $stockin_time
 * @property string $stockin_time_text
 * @property int $stockin_batch_id
 * @property int $stockin_order_status
 * @property int $stockin_order_status_text
 * @property int $client_id
 * @property int $client_name
 * @property string $client_contact
 * @property string $client_mobile
 * @property int $sku_id
 * @property string $upc_id
 * @property string $sku_name
 * @property int $sku_category_1
 * @property int $sku_category_2
 * @property int $sku_category_3
 * @property string $sku_category_1_text
 * @property string $sku_category_2_text
 * @property string $sku_category_3_text
 * @property int $sku_from_country
 * @property string $sku_from_country_text
 * @property string $sku_net
 * @property int $upc_unit
 * @property int $upc_unit_text
 * @property int $upc_unit_num
 * @property string $expire_date
 * @property int $stockin_order_real_amount
 * @property int $sku_price
 * @property int $sku_price_tax
 * @property int $stockin_order_sku_total_price
 * @property int $stockin_order_sku_total_price_tax
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinStockoutDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinStockoutDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinStockoutDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinStockoutDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinStockoutDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinStockoutDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_StockinStockoutDetail extends Order_Base_Orm
{

    public static $tableName = 'stockin_stockout_detail';
    public static $dbName = 'nwms_statistics';
    public static $clusterName = 'nwms_statistics_cluster';

    /**
     * 获取销退入库明细（分页）
     *
     * @param array $arrWarehouseId
     * @param integer $intStockinOrderId
     * @param integer $intSourceOrderId
     * @param integer $intSkuId
     * @param integer $intClientId
     * @param string $strClientName
     * @param array $arrStockinTime
     * @param integer $intPageNum
     * @param integer $intPageSize
     * @return mixed
     */
    public static function getStockoutStockinDetail(
        $arrWarehouseId,
        $intStockinOrderId,
        $intSourceOrderId,
        $intSkuId,
        $intClientId,
        $strClientName,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        // 必传仓库id
        $arrCondition['warehouse_id'] = ['in', $arrWarehouseId];

        if (!empty($intStockinOrderId)) {
            $arrCondition['stockin_order_id'] = $intStockinOrderId;
        }

        if (!empty($intSourceOrderId)) {
            $arrCondition['source_order_id'] = $intSourceOrderId;
        }

        if (!empty($intSkuId)) {
            $arrCondition['sku_id'] = $intSkuId;
        }

        if (!empty($intClientId)) {
            $arrCondition['client_id'] = $intClientId;
        }

        if (!empty($strClientName)) {
            $arrCondition['client_name'] = [
                'like',
                $strClientName . '%',
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

        // 固定添加条件，只查询入库类型为 销退入库 的数据
        $arrCondition['stockin_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;

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
}

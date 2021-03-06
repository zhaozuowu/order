<?php

/**
 * @property int $id
 * @property int $stockin_order_id
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
 * @property int $stockin_order_sku_total_price
 * @property int $stockin_order_sku_total_price_tax
 * @property int $reserve_order_sku_plan_amount
 * @property int $stockin_order_sku_real_amount
 * @property string $stockin_order_sku_extra_info
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockinOrderSku extends Order_Base_Orm
{

    public static $tableName = 'stockin_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * batch create stock in order sku
     * @param array $arrStockinOrderSkus
     * @param int $intStockinOrderId
     * @return int
     */
    public static function batchCreateStockinOrderSku($arrStockinOrderSkus, $intStockinOrderId)
    {
        $arrDb = [];
        foreach ($arrStockinOrderSkus as $arrRow) {
            $arrDb[] = [
                'stockin_order_id' => intval($intStockinOrderId),
                'sku_id' => intval($arrRow['sku_id']),
                'upc_id' => strval($arrRow['upc_id']),
                'upc_unit' => intval($arrRow['upc_unit']),
                'upc_unit_num' => intval($arrRow['upc_unit_num']),
                'sku_name' => strval($arrRow['sku_name']),
                'stockin_reason' => intval($arrRow['stockin_reason']),
                'sku_net' => strval(floatval($arrRow['sku_net'])),
                'sku_net_unit' => intval($arrRow['sku_net_unit']),
                'sku_net_gram' => strval(floatval($arrRow['sku_net_gram'])),
                'sku_price' => intval($arrRow['sku_price']),
                'sku_price_tax' => intval($arrRow['sku_price_tax']),
                'sku_tax_rate' => intval($arrRow['sku_tax_rate'] ?? 0),
                'sku_effect_type' => intval($arrRow['sku_effect_type'] ?? 0),
                'sku_effect_day' => intval($arrRow['sku_effect_day'] ?? 0),
                'sku_from_country' => intval($arrRow['sku_from_country'] ?? 0),
                'stockin_order_sku_total_price' => intval($arrRow['stockin_order_sku_total_price']),
                'stockin_order_sku_total_price_tax' => intval($arrRow['stockin_order_sku_total_price_tax']),
                'reserve_order_sku_plan_amount' => intval($arrRow['reserve_order_sku_plan_amount']),
                'stockout_order_sku_amount' => intval($arrRow['stockout_order_sku_amount']),
                'stockin_order_sku_real_amount' => intval($arrRow['stockin_order_sku_real_amount']),
                'stockin_order_sku_extra_info' => strval($arrRow['stockin_order_sku_extra_info']),
                'sku_main_image' => strval($arrRow['sku_main_image']),
                'upc_min_unit' => intval($arrRow['upc_min_unit']),
            ];
        }
        return self::batchInsert($arrDb);
    }

    /**
     * 获取入库单商品列表（分页）
     *
     * @param $intStockinOrderId
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     */
    public static function getStockinOrderSkuList(
        $intStockinOrderId,
        $intPageNum,
        $intPageSize)
    {
        $arrResult = [
            'total' => '0',
            'list' => [],
        ];

        if (empty($intStockinOrderId)) {
            return $arrResult;
        }

        // 只查询未软删除的
        $arrCondition = [
            'stockin_order_id' => $intStockinOrderId,
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
     * get batch stockin order sku list
     * @param $arrStockinOrderIds
     * @return array
     */
    public static function getBatchStockinOrderSkuList($arrStockinOrderIds)
    {
        $arrCondition = [
            'stockin_order_id' => ['in', $arrStockinOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrOrderBy = ['id' => 'asc'];
        return self::findRows(self::getAllColumns(), $arrCondition, $arrOrderBy);
    }

    /**
     * 获取指定入库单的sku_id商品信息
     * @param $strStockinOrderId
     * @param $strSkuId
     * @return array
     */
    public static function getStockinOrderSkuInfo($strStockinOrderId, $strSkuId)
    {
        $arrConds = [
            'stockin_order_id' => $strStockinOrderId,
            'sku_id' => $strSkuId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrResult = self::findRow(self::getAllColumns(), $arrConds);
        return $arrResult;
    }

    /**
     * 获取入库单商品列表（不分页）
     * @param $intStockinOrderId
     * @return array
     */
    public static function getStockinOrderSkus($intStockinOrderId)
    {
        $arrResult = [
            'total' => '0',
            'list' => [],
        ];

        if (empty($intStockinOrderId)) {
            return $arrResult;
        }

        // 只查询未软删除的
        $arrCondition = [
            'stockin_order_id' => $intStockinOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];

        // 排序条件
        $orderBy = ['sku_id' => 'asc'];

        // 查找满足条件的所有列数据
        $arrCols = self::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = self::findRowsAndTotalCount(
            $arrCols,
            $arrCondition,
            $orderBy
        );

        $arrResult['total'] = $arrRowsAndTotal['total'];
        $arrResult['list'] = $arrRowsAndTotal['rows'];
        return $arrResult;
    }

    /**
     * 通过入库单号数组获取入库单商品
     * @param $arrStockinOrderIds
     * @return array
     */
    public static function getStockinOrderSkusByStockinOrderIds($arrStockinOrderIds)
    {
        if (empty($arrStockinOrderIds)) {
            return [];
        }
        $arrConditions = [
            'stockin_order_id' => ['in', $arrStockinOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrCols = self::getAllColumns();
        return self::findRows($arrCols, $arrConditions);
    }

    /**
     * 确认销退入库单sku修改信息
     * @param  array $arrDbSkuInfoList
     */
    public static function confirmStockInOrderSkuList($arrDbSkuInfoList)
    {
        foreach ($arrDbSkuInfoList as $arrDbSkuInfo) {
            $arrCondition = [
                'stockin_order_id' => $arrDbSkuInfo['stockin_order_id'],
                'sku_id' => $arrDbSkuInfo['sku_id'],
            ];
            $arrUpdateInfo = [
                'stockin_order_sku_real_amount' => $arrDbSkuInfo['stockin_order_sku_real_amount'],
                'stockin_order_sku_extra_info' => $arrDbSkuInfo['stockin_order_sku_extra_info'],
            ];
            self::findOne($arrCondition)->update($arrUpdateInfo);
        }
    }

    /**
     * 批量获取入库单商品列表（不分页）
     * @param $arrStockinOrderIds
     * @return array
     */
    public static function getBatchStockinOrderSkus($arrStockinOrderIds)
    {
        $arrResult = [
            'total' => '0',
            'list' => [],
        ];

        if (empty($arrStockinOrderIds)) {
            return $arrResult;
        }

        // 只查询未软删除的
        $arrCondition = [
            'stockin_order_id' => [
                'in',
                $arrStockinOrderIds,
            ],

            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];

        // 排序条件
        $orderBy = ['stockin_order_id' => 'asc'];

        // 查找满足条件的所有列数据
        $arrCols = self::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = self::findRowsAndTotalCount(
            $arrCols,
            $arrCondition,
            $orderBy
        );

        $arrResult['total'] = $arrRowsAndTotal['total'];
        $arrResult['list'] = $arrRowsAndTotal['rows'];
        return $arrResult;
    }

    /**
     * 获取入库单商品信息
     * @param $intStockinOrderId
     * @param $intSkuId
     * @return Model_Orm_StockinOrderSku
     */
    public static function getStockinOrderSkuInfoObject($intStockinOrderId, $intSkuId)
    {
        $arrContionds = [
            'stockin_order_id' => $intStockinOrderId,
            'sku_id' => $intSkuId,
            'is_delete' => Nscm_Define_Const::ENABLE,
        ];

        return self::findOne($arrContionds);
    }
}

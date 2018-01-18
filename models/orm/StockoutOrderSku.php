<?php

/**
 * @property int $id
 * @property int $stockout_order_id
 * @property string $upc_id
 * @property int $sku_id
 * @property int $order_amount
 * @property int $distribute_amount
 * @property int $pickup_amount
 * @property string $sku_name
 * @property int $cost_price
 * @property int $cost_total_price
 * @property string $sku_net
 * @property int $upc_unit
 * @property int $upc_unit_num
 * @property int $send_price
 * @property int $send_total_price
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_StockoutOrderSku findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockoutOrderSku[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockoutOrderSku[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockoutOrderSku findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockoutOrderSku[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockoutOrderSku[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_StockoutOrderSku extends Order_Base_Orm
{

    public static $tableName = 'stockout_order_sku';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
    public static $arrGroupByColumns = [
        'sku_id',
        'sku_name',
        'upc_id',
        'sku_net',
        'sku_net_unit',
        'upc_unit',
        'sum(pickup_amount) as pickup_amount',
    ];

    /**
     * 更新出库单sku信息
     * @param $condition 查询条件
     * @param $updateData 要更新数据
     * @return bool|int|mysqli|null
     */
    public function updateStockoutOrderStatusByCondition($condition, $updateData)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderInfo = $this->findOne($condition);
        if (empty($stockoutOrderInfo)) {
            return false;
        }
        $res = $stockoutOrderInfo->update($updateData);
        return $res;
    }


    /**
     * 根据出库单号获取出库sku信息
     * @param $stockoutOrderId 出库单号
     * @return array
     */
    public function getSkuInfoById($stockoutOrderId, $columns = '')
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return [];
        }

        if (empty($columns)) {
            $columns = self::getAllColumns();
        }
        $condition = ['stockout_order_id' => $stockoutOrderId];
        $stockoutOrderSkuInfo = $this->find($condition)->select($columns)->rows();
        if (empty($stockoutOrderSkuInfo)) {
            return [];
        }
        Bd_Log::debug(__METHOD__ . ' return: ' . json_encode($stockoutOrderSkuInfo));
        return $stockoutOrderSkuInfo;
    }


    /* 
     * 根据order_id批量获取商品信息 
     * @param array $arrOrderIds
     * @return array
     */

    public function getStockoutOrderSkusByOrderIds($arrOrderIds)
    {

        if (empty($arrOrderIds)) {
            return [];
        }
        $arrConditions = [];
        $arrConditions['stockout_order_id'] = ['in', $arrOrderIds];
        $arrColumns = self::getAllColumns();
        return Model_Orm_StockoutOrderSku::findRows($arrColumns, $arrConditions, ['id' => 'asc']);
    }

    /**
     * @param array $arrConditions
     * @param $intPageSize
     * @param $intPageNum
     * @return array
     * @throws Order_BusinessError
     */
    public static function getListByConditions($arrConditions, $intPageSize, $intPageNum)
    {
        $intLimit = intval($intPageSize);
        $intOffset = ($intPageNum - 1)*$intLimit;
        if ($intOffset < 0 || $intLimit < 0) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrColumns = self::getAllColumns();
        return Model_Orm_StockoutOrderSku::findRows($arrColumns, $arrConditions, ['id' => 'asc'],
                                                    $intOffset, $intLimit);
    }

    /**
     * get group list
     * @param $arrConditions
     * @param $strGroupKey
     * @param array $arrColumns
     * @return mixed
     */
    public static function getGroupList($arrConditions, $strGroupKey, $arrColumns=[]) {
        if (empty($arrColumns)) {
            $arrColumns = self::$arrGroupByColumns;
        }
        $arrRetList = Model_Orm_StockoutOrderSku::find($arrConditions)
            ->select($arrColumns)
            ->groupBy([$strGroupKey])
            ->orderBy(['id' => 'asc'])
            ->rows();
        return $arrRetList;
    }
}

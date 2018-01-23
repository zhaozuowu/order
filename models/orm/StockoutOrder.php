<?php

/**
 * @property int $id
 * @property int $stockout_order_id
 * @property int $stockout_order_status
 * @property int $destroy_order_status
 * @property int $business_form_order_id
 * @property int $stockout_order_type
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $stockout_order_source
 * @property int $expect_send_time
 * @property int $stockout_order_amount
 * @property int $stockout_order_distribute_amount
 * @property int $stockout_order_pickup_amount
 * @property string $stockout_order_remark
 * @property string $customer_name
 * @property int $customer_id
 * @property string $customer_contactor
 * @property int $stockout_order_total_price
 * @property string $customer_contact
 * @property string $customer_address
 * @property int $stockout_order_is_print
 * @property int $create_time
 * @property int $update_time
 * @property int $is_delete
 * @property int $version
 * @method static Model_Orm_StockoutOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockoutOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockoutOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockoutOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockoutOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockoutOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_StockoutOrder extends Order_Base_Orm
{

    public static $tableName = 'stockout_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';
    /**
     * 根据出库单号获取出库单信息
     * @param $stockoutOrderId 出库单号
     * @return array
     */
    public function getStockoutOrderInfoById($stockoutOrderId)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return [];
        }

        $condition = ['stockout_order_id' => $stockoutOrderId];
        $stockoutOrderInfo = $this->findOne($condition);
        if (empty($stockoutOrderInfo)) {
            return [];
        }
        $stockoutOrderInfo = $stockoutOrderInfo->toArray();

        Bd_Log::debug(__METHOD__ . ' return: ' . json_encode($stockoutOrderInfo));
        return $stockoutOrderInfo;


    }

    /**
     * 通过出库单号查询运单号
     * @param array $intStockoutOrderId
     * @return array|mixed
     * @throws Order_BusinessError
     */
    public static function getShipmentOrderIdByStockoutOrderId($intStockoutOrderId) {
        if (empty($intStockoutOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $arrConditions = [
            'stockout_order_id' => $intStockoutOrderId,
        ];
        $objStockoutOrder = static::findOne($arrConditions);
        if (!$objStockoutOrder) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        return $objStockoutOrder->shipment_order_id;
    }

    /**
     * 更新数据
     * @param $arrConditions
     * @param $updateData
     * @return bool|int|mysqli|null
     */
    public  function updateDataByConditions($arrConditions, $updateData)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        if (empty($arrConditions)) {
            return false;
        }
        $res  = $this->find($arrConditions)->update($updateData);
        return $res;
    }

    /**
     * 根据出库单号更新出库单状态
     * @param $stockoutOrderId
     * @param $updateData
     * @return bool|int|mysqli|null
     */
    public function updateStockoutOrderStatusById($stockoutOrderId, $updateData)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return false;
        }
        $condition = ['stockout_order_id' => $stockoutOrderId];
        $stockoutOrderInfo = $this->findOne($condition);
        if (empty($stockoutOrderInfo)) {
            return false;
        }
        $res = $stockoutOrderInfo->update($updateData);
        return $res;
    }
}

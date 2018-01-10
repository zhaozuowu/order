<?php

/**
 * @property int $id
 * @property int $stockout_order_id
 * @property int $business_form_order_id
 * @property int $stockout_order_status
 * @property string $stockout_order_status_describle
 * @property string $city_name
 * @property int $city_id
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $stockout_order_type
 * @property string $stockout_order_type_describle
 * @property int $order_type
 * @property string $order_type_describle
 * @property int $order_create_time
 * @property int $expect_delivery_time
 * @property string $customer_name
 * @property int $customer_id
 * @property string $customer_contactor
 * @property string $customer_contact
 * @property int $sku_id
 * @property string $upc_id
 * @property string $sku_name
 * @property int $category_1
 * @property int $category_2
 * @property int $category_3
 * @property string $category_1_text
 * @property string $category_2_text
 * @property string $category_3_text
 * @property int $import
 * @property string $import_describle
 * @property string $sku_net
 * @property int $sku_net_unit
 * @property int $sku_net_gram
 * @property int $upc_unit
 * @property string $upc_unit_text
 * @property int $order_amount
 * @property int $distribute_amount
 * @property int $pickup_amount
 * @property string $effect_date
 * @property int $cost_price
 * @property int $cost_price_tax
 * @property int $cost_total_price
 * @property int $cost_total_price_tax
 * @property int $send_price
 * @property int $send_price_tax
 * @property int $send_total_price
 * @property int $send_total_price_tax
 * @property int $waybill_order_id
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockoutOrderDetail findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockoutOrderDetail[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockoutOrderDetail[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockoutOrderDetail findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockoutOrderDetail[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockoutOrderDetail[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
*/

class Model_Orm_StockoutOrderDetail extends Order_Base_Orm
{

    public static $tableName = 'stockout_order_detail';
    public static $dbName = 'nwms_statistics';
    public static $clusterName = 'nwms_statistics_cluster';

    /**
     * 获取销售出库明细
     * @param $arrConditions
     * @param array $arrColumns
     * @param null $intOffset
     * @param null $intLimit
     * @return array
     */
    public static function getStockoutDetailByConditions($arrConditions, $arrColumns = [], $intOffset = null, $intLimit = null)
    {
        if (empty($arrColumns)) {
            $arrColumns = self::getAllColumns();
        }
        $list =  self::findRows($arrColumns, $arrConditions, ['create_time' => 'desc'], $intOffset, $intLimit);
        return $list;
    }
}

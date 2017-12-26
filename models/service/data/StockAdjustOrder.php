<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_StockAdjustOrder
{
    public function create($arrInput) {

        $intNow = time();

        $arrOrder = [
            'stock_adjust_order_id'  => 12345,
            'warehouse_id'  => $arrInput['warehouse_id'],
            'warehouse_name'  => $arrInput['warehouse_name'],
//            'total_adjust_amount'  => ['type' => Wm_Orm_ColumnType::INTEGER, 'default' => 0, ],
//            'adjust_type'  => ['type' => Wm_Orm_ColumnType::INTEGER, 'default' => 0, ],
//            'remark'  => ['type' => Wm_Orm_ColumnType::STRING, 'default' => '', ],
//            'creator'  => ['type' => Wm_Orm_ColumnType::INTEGER, 'default' => 0, ],
//            'creator_name'  => ['type' => Wm_Orm_ColumnType::STRING, 'default' => '', ],
            'is_delete'  => 1,
            'create_time'  => $intNow,
            'update_time'  => $intNow,
            'version'  => 1,
        ];


        $arrOutput = Model_Orm_StockAdjustOrder::getConnection()->transaction(function () use ($arrOrder) {
            return Model_Orm_StockAdjustOrder::create($arrOrder);
        });
        return $arrOutput;
    }

    public function get($cond, $orderBy = [], $offset = 0, $limit = null) {

        $_arrCols = [
            'stock_adjust_order_id',
            'adjust_type',
            'remark',
            'create_time',
            'creator_name',
        ];
        $ret = Model_Orm_StockAdjustOrder::findRows($_arrCols, $cond, $orderBy, $offset, $limit);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . json_encode($ret));
        return $ret;
    }

    public function getCount($cond) {
        $ret = Model_Orm_StockAdjustOrder::count( $cond);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . $ret);
        return $ret;
    }
}
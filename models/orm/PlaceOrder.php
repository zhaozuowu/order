<?php
/**
 * @desc 上架单
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Model_Orm_PlaceOrder extends Order_Base_Orm
{

    public static $tableName   = 'place_order';
    public static $dbName      = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 获取上架单状态统计，只查询未软删除的
     *
     * @return array
     */
    public static function getPlaceOrderStatistics()
    {
        $arrCond = ['is_delete' => Order_Define_Const::NOT_DELETE];
        $arrResult = self::find($arrCond)
            ->select(['place_order_status', 'count(*) as place_order_status_count'])
            ->groupBy(['place_order_status'])
            ->orderBy(['place_order_status' => 'desc'])
            ->rows();

        return $arrResult;
    }

    /**
     * 通过上架单号获取上架单信息
     * @param $intPlaceOrderId
     * @return array
     */
    public static function getPlaceOrderInfoByPlaceOrderId($intPlaceOrderId)
    {
        if (empty($intPlaceOrderId)) {
            return [];
        }
        $arrConditions = [
            'place_order_id' => $intPlaceOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrCols = self::getAllColumns();
        return self::findRow($arrCols, $arrConditions);
    }

    /**
     * 获取上架单分页列表
     * @param $arrConditions
     * @param $intLimit
     * @param $intOffset
     * @return mixed
     */
    public static function getPlaceOrderList($arrConditions, $intLimit, $intOffset)
    {
        $arrCols = self::getAllColumns();
        return self::findRows($arrCols, $arrConditions, ['id' => 'asc'], $intOffset, $intLimit);
    }


}

<?php
/**
 * @desc 上架单
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Model_Orm_PlaceOrder extends Order_Base_Orm
{

    public static $tableName = 'place_order';
    public static $dbName = 'nwms_order';
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


}

<?php
/**
 * @desc 上架单详情
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */


class Model_Orm_PlaceOrderDetail extends Order_Base_Orm
{

    public static $tableName = 'place_order_detail';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * 获取业态订单sku信息
     * @param $arrConditions
     * @param array $arrColumns
     * @param null $intOffset
     * @param null $intLimit
     * @return array
     */
    public static function getBusSkuListByConditions($arrConditions, $arrColumns = [], $intOffset = null, $intLimit = null)
    {
        if (empty($arrColumns)) {
            $arrColumns = self::getAllColumns();
        }
        return self::findRows($arrColumns, $arrConditions, ['create_time' => 'desc'], $intOffset, $intLimit);
    }

}
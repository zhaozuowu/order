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
            'is_auto' => Order_Define_PlaceOrder::PLACE_ORDER_NOT_AUTO,
        ];
        $arrCols = self::getAllColumns();
        return self::findRow($arrCols, $arrConditions);
    }

    /**
     * 通过上架单号获取上架单信息不过滤是否自动
     * @param $intPlaceOrderId
     * @return array
     */
    public static function getAllPlaceOrderInfoByPlaceOrderId($intPlaceOrderId)
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
     * 通过上架单号数组批量获取上架单信息
     * @param $arrPlaceOrderIds
     * @return array
     */
    public static function getPlaceOrderInfosByPlaceOrderIds($arrPlaceOrderIds)
    {
        if (empty($arrPlaceOrderIds)) {
            return [];
        }
        $arrConditions = [
            'place_order_id' => ['in', $arrPlaceOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrCols = self::getAllColumns();
        return self::findRows($arrCols, $arrConditions);
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
        return self::findRows($arrCols, $arrConditions, ['id' => 'desc'], $intOffset, $intLimit);
    }

    /**
     * 确认上架单
     * @param $intPlaceOrderId
     * @param $strUserName
     * @param $intUserId
     * @return bool
     */
    public static function placeOrder($intPlaceOrderId, $strUserName, $intUserId)
    {
        if (empty($intPlaceOrderId)) {
            return false;
        }
        $arrConditions = [
            'place_order_id' => $intPlaceOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $objPlaceOrderInfo = self::findOne($arrConditions);
        $objPlaceOrderInfo->place_order_status = Order_Define_PlaceOrder::STATUS_PLACED;
        $objPlaceOrderInfo->confirm_user_id = $intUserId;
        $objPlaceOrderInfo->confirm_user_name = $strUserName;
        $objPlaceOrderInfo->confirm_time = time();
        $objPlaceOrderInfo->update();
        return true;
    }
}

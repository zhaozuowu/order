<?php
/**
 * @desc 上架单详情
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */
class Model_Orm_PlaceOrderSku extends Order_Base_Orm
{

    public static $tableName = 'place_order_sku';
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

    /**
     * 通过上架单号获取上架单sku信息
     * @param $intPlaceOrderId
     * @return array
     */
    public static function getPlaceOrderSkusByPlaceOrderId($intPlaceOrderId,$orderBy = [])
    {
        if (empty($intPlaceOrderId)) {
            return [];
        }
        $arrCols = self::getAllColumns();
        $arrConditions = [
            'place_order_id' => $intPlaceOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return self::findRows($arrCols, $arrConditions,$orderBy);
    }

    /**
     * 通过上架单号数组批量获取sku信息
     * @param $arrPlaceOrderIds
     * @return array
     */
    public static function getPlaceOrderSkusByPlaceOrderIds($arrPlaceOrderIds,$orderBy=[])
    {
        if (empty($arrPlaceOrderIds)) {
            return [];
        }
        $arrCols = self::getAllColumns();
        $arrConditions = [
            'place_order_id' => ['in', $arrPlaceOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        return Model_Orm_PlaceOrderSku::findRows($arrCols, $arrConditions,$orderBy);
    }

    /**
     * 确认上架单
     * @param $intPlaceOrderId
     * @param $arrPlacedSkus
     * @param $strUserName
     * @param $intUserId
     * @return bool
     */
    public static function updatePlaceOrderActualInfo($intPlaceOrderId, $arrPlacedSkus)
    {
        if (empty($intPlaceOrderId)) {
            return false;
        }
        $arrPlaceOrderInfo = Model_Orm_PlaceOrder::getAllPlaceOrderInfoByPlaceOrderId($intPlaceOrderId);
        if (empty($arrPlaceOrderInfo)) {
            return false;
        }
        foreach ((array)$arrPlacedSkus as $strKey => $arrPlacedSkuItem) {
            $arrKey = explode('#', $strKey);
            $intSkuId = $arrKey[0];
            $intExpireDate = $arrKey[1];
            if (empty($intSkuId)) {
                continue;
            }
            $arrConditions = [
                'place_order_id' => $intPlaceOrderId,
                'sku_id' => $intSkuId,
                'expire_date' => $intExpireDate,
            ];
            $arrCols = [
                'actual_info' => json_encode($arrPlacedSkuItem),
            ];
            self::updateAll($arrCols, $arrConditions);
        }
        return true;
    }

}
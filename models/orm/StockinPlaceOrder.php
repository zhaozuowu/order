<?php
/**
 * @name StockinPlaceOrder.php
 * @desc StockinPlaceOrder.php
 * @author yu.jin03@ele.me
 */

class Model_Orm_StockinPlaceOrder extends Order_Base_Orm
{
    public static $tableName = 'stockin_place_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * get place order ids by stockin order ids
     * @param $arrStockinOrderIds
     * @return array
     */
    public static function getPlaceOrdersByStockinOrderIds($arrStockinOrderIds)
    {
        if (empty($arrStockinOrderIds)) {
            return [];
        }
        $arrConditions = [
            'stockin_order_id' => ['in', $arrStockinOrderIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrRet = self::findRows(['place_order_id'], $arrConditions);
        return array_column($arrRet, 'place_order_id');
    }

    /**
     * get place order ids by fuzzy stockin order id
     * @param $intFuzzyStockinOrderId
     * @return array
     */
    public static function getPlaceOrderIdsByFuzzyStockinOrderId($intFuzzyStockinOrderId)
    {
        if (empty($intFuzzyStockinOrderId)) {
            return [];
        }
        $arrConditions = [
            'stockin_order_id%10000' => $intFuzzyStockinOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrRet = self::findRows(['place_order_id'], $arrConditions);
        return array_column($arrRet, 'place_order_id');
    }

    /**
     * get stockin order ids by place order id
     * @param $intPlaceOrderId
     * @return array
     */
    public static function getStockinOrderIdsByPlaceOrderId($intPlaceOrderId)
    {
        if (empty($intPlaceOrderId)) {
            return [];
        }
        $arrConditions = [
            'place_order_id' => $intPlaceOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $arrRet = self::findRows(['stockin_order_id'], $arrConditions);
        return array_column($arrRet, 'stockin_order_id');
    }

}
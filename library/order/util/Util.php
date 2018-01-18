<?php
/**
 * @name Order_Util_Util
 * @desc Order_Util_Util
 * @author lvbochao@iwaimai.baidu.com
 */
class Order_Util_Util
{

    /**
     * generate reserve order code
     * @return int
     */
    public static function generateReserveOrderCode()
    {
        return Nscm_Lib_IdGenerator::sequenceDateNumber();
    }

    /**
     * generate stockin order code
     * @return int
     */
    public static function generateStockinOrderCode()
    {
        return Nscm_Lib_IdGenerator::sequenceDateNumber();
    }

    /**
     * generate stockout order id
     * @return void
     */
    public static function generateStockoutOrderId() 
    {
        return Nscm_Lib_IdGenerator::sequenceDateNumber();
    }

    /**
     * generate stock adjust order id
     * @return int
     */
    public static function generateStockAdjustOrderId()
    {
        return Nscm_Lib_IdGenerator::sequenceDateNumber();
    }

    /**
     * geenerate business form order id
     * @return void
     */
    public static function generateBusinessFormOrderId() 
    {
        return NScm_Lib_IdGenerator::sequenceDateNumber();
    }

    /**
     * transfer array to key value pair
     * @param array $arr
     * @param string $primary_key
     * @return array
     */
    public static function arrayToKeyValue($arr, $primary_key) {
        if (empty($arr) || empty($primary_key)) {
            return array();
        }
        $arrKeyValue = array();
        foreach ($arr as $item) {
            if(isset($item[$primary_key])) {
                $arrKeyValue[$item[$primary_key]] = $item;
            }
        }
        return $arrKeyValue;
    }

    /**
     * transfer array to key values pair
     * @param array $arr
     * @param string $primary_key
     * @return array
     */
    public static function arrayToKeyValues($arr, $primary_key) {
        if (empty($arr) || empty($primary_key)) {
            return array();
        }
        $arrKeyValue = array();
        foreach ($arr as $item) {
            if(isset($item[$primary_key])) {
                $arrKeyValue[$item[$primary_key]][] = $item;
            }
        }
        return $arrKeyValue;
    }
}
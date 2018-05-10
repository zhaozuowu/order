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
     * generate stock frozen order id
     * @return int
     */
    public static function generateStockFrozenOrderId()
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
     * generate pickup order id
     * @return void
     */
    public static function generatePickupOrderId()
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

    /**
     * 把百度地图坐标转成高德地图坐标
     * @param $strLocation
     * @return string
     */
    public static function transferBMapToAMap($strLocation) {
        $strRetLocation = '';
        $arrLocation = explode(',', $strLocation);
        if (empty($arrLocation)) {
            return $strLocation;
        }
        $arrRetLocation = Wm_Lib_Coord::convert(
            $arrLocation,
            Wm_Lib_Coord::TYPE_BDLL,
            Wm_Lib_Coord::TYPE_AMAP
        );
        $strRetLocation = implode(',', $arrRetLocation);
        return $strRetLocation;
    }

    /**
     *
     * @param array $arrStockoutOrderIds
     * @return array
     */
    public static function batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds)
    {
        foreach ((array)$arrStockoutOrderIds as $intKey => $strStockoutOrderId) {
            $arrStockoutOrderIds[$intKey] = self::trimStockoutOrderIdPrefix($strStockoutOrderId);
        }
        return $arrStockoutOrderIds;
    }

    /**
     * 过滤出库单前缀
     * @param $strStockoutOrderId
     * @return string
     */
    public static function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        return ltrim($strStockoutOrderId, Nscm_Define_OrderPrefix::SOO);
    }

}
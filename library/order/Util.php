<?php

/**
 * @name Order_Util
 * @desc APP公共工具类
 * @author nscm
 */
class Order_Util
{
    /**
     * 对参数时间进行校验，结束时间应该在开始时间之后或者相等
     * 均为正整数
     * 均为空返回true
     * @param $startTime
     * @param $endTime
     * @return bool
     */
    public static function verifyUnixTimeSpan($startTime, $endTime)
    {
        // 均为空
        if (empty($startTime) && empty($endTime)) {
            return true;
        }

        // 判断非负
        if ((0 > $startTime) || (0 > $endTime)) {
            return false;
        }

        // 结束时间判断
        if ($startTime <= $endTime) {
            return true;
        }

        return false;
    }

    /**
     * 对输入的Unix时间戳进行格式转换
     * @param $unixTime
     * @return false|string
     */
    public static function getFormatDateTime($unixTime)
    {
        return date('Y-m-d H:i:s', intval($unixTime));
    }

    /**
     * 返回格式化表示的当前时间
     * @return int
     */
    public static function getNowDateTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 返回当前时间的Unix时间戳
     * @return int
     */
    public static function getNowUnixDateTime()
    {
        return time();
    }

    /**
     * 对输入的字符串进行分解，返回拆分后的整数数组
     *
     * @param $strInput
     * @return array|null
     */
    public static function extractIntArray($strInput)
    {
        if (empty($strInput)) {
            return null;
        }

        $strIn = trim($strInput);

        // parse array
        $arrResult = explode(',', $strIn);
        foreach ($arrResult as $key => $item) {
            $arrResult[$key] = intval($item);
        }

        return $arrResult;
    }

    /**
     * 去除采购单开头的PUR开头部分内容
     * @param $strOrderId
     * @return bool|string
     */
    public static function trimPurchaseOrderIdPrefix($strOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strOrderId, Nscm_Define_OrderPrefix::PUR);

        return $strResult;
    }

    /**
     * 去除预约入库单开头的ASN开头部分内容
     * @param $strReserveOrderId
     * @return bool|string
     */
    public static function trimReserveOrderIdPrefix($strReserveOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strReserveOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strReserveOrderId, Nscm_Define_OrderPrefix::ASN);

        return $strResult;
    }

    /**
     * 去除出库单号SSO开头部分内容
     * @param $strStockoutOrderId
     * @return bool|string
     */
    public static function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strStockoutOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strStockoutOrderId, Nscm_Define_OrderPrefix::SSO);

        return $strResult;
    }
}

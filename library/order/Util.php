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
     * 另外，默认的查询时间区间不能超出90天时间范围
     * 均为正整数
     * 均为空返回true
     * @param integer $intStartTime
     * @param integer $intEndTime
     * @param float|int $intMaxInterval
     * @return bool
     */
    public static function verifyUnixTimeSpan($intStartTime, $intEndTime, $intMaxInterval = 90 * 86400)
    {
        // 均为空认为通过
        if (empty($intStartTime) && empty($intEndTime)) {
            return true;
        }
        // 判断非负
        if ((0 > $intStartTime) || (0 > $intEndTime)) {
            return false;
        }
        // 结束时间判不能早于开始时间
        if ($intStartTime > $intEndTime) {
            return false;
        }
        // 如果给定的时间范围为0则不校验区间
        if (0 == $intMaxInterval){
            return true;
        }
        // 查询区间长度范围在给定时间范围内
        if ($intMaxInterval < ($intEndTime - $intStartTime)){
            return false;
        }

        return true;
    }

    /**
     * 对输入的Unix时间戳进行格式转换
     * 如果输入为0则返回默认的空字符串格式
     * @param integer $unixTime
     * @return false|string
     */
    public static function getFormatDateTime($unixTime)
    {
        if(0 == $unixTime){
            return Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
        }

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
     * 去除预约单开头的ASN开头部分内容
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
     * 去除入库单开头的SIO开头部分内容
     *
     * @param $strStockinOrderId
     * @return string
     */
    public static function trimStockinOrderIdPrefix($strStockinOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strStockinOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strStockinOrderId, Nscm_Define_OrderPrefix::SIO);

        return $strResult;
    }

    /**
     * 去除出库单开头的SOO开头部分内容
     *
     * @param $strStockoutOrderId
     * @return string
     */
    public static function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strStockoutOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strStockoutOrderId, Nscm_Define_OrderPrefix::SOO);

        return $strResult;
    }

    /**
     * 去除库存调整单开头的SAO前缀
     *
     * @param $strStockAdjustOrderId
     * @return string
     */
    public static function trimStockAdjustOrderIdPrefix($strStockAdjustOrderId)
    {
        // 返回结果默认为空
        $strResult = '';

        if (empty($strStockAdjustOrderId)) {
            return $strResult;
        }

        $strResult = ltrim($strStockAdjustOrderId, Nscm_Define_OrderPrefix::SAO);

        return $strResult;
    }

    /**
     * 判断value的值是否在数组中
     * 遇到空参数返回错误
     *
     * @param $value
     * @param $arr
     * @return bool
     */
    public static function valueIsInArray($value, $arr)
    {
        if (empty($value)) {
            return false;
        }

        if (empty($arr)) {
            return false;
        }

        foreach ($arr as $item) {
            if ($value === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * 分解获取关联入库单的单号，只处理ASN和SOO两种订单号，否则返回null
     * 根据单号前缀判断输入单类型
     * 如果订单号前缀类型不在给定的数组内则抛出参数错误异常
     * [source_order_id, source_order_type]
     *
     * @param $strSourceOrderId
     * @return null|array[source_order_id, source_order_type]
     */
    public static function parseSourceOrderId($strSourceOrderId)
    {
        if (empty($strSourceOrderId)) {
            return null;
        }

        // preg_match('/^ASN\d{13}$/', $strSourceOrderId)
        if (!empty(preg_match('/^' . Nscm_Define_OrderPrefix::ASN . '\d{13}$/', $strSourceOrderId))) {
            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimReserveOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        // preg_match('/^SOO\d{13}$/', $strSourceOrderId)
        if (!empty(preg_match('/^' . Nscm_Define_OrderPrefix::SOO . '\d{13}$/', $strSourceOrderId))) {
            $arrSourceOrderIdInfo['source_order_type'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
            $arrSourceOrderIdInfo['source_order_id'] = intval(Order_Util::trimStockoutOrderIdPrefix($strSourceOrderId));
            return $arrSourceOrderIdInfo;
        }

        return null;
    }
}

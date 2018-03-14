<?php
/**
 * Order_Exception_Collector
 * User: bochao.lv
 * Date: 2018/3/9
 * Time: 14:37
 */

class Order_Exception_Collector
{
    /**
     * exceptions
     * @var Order_Exception_Exception[]
     */
    private static $exceptions = [];

    /**
     * add exception
     * @param $intOrderId
     * @param $intSkuId
     * @param $strSkuName
     * @param $intExceptionTypeConcrete
     * @param $intExceptionLevel
     * @param string $strExceptionInfo
     */
    public static function addException($intOrderId,
                                        $intSkuId,
                                        $strSkuName,
                                        $intExceptionTypeConcrete,
                                        $intExceptionLevel = 0,
                                        $strExceptionInfo = '')
    {
        $exception = new Order_Exception_Exception($intOrderId,
            $intSkuId,
            $strSkuName,
            $intExceptionTypeConcrete,
            $intExceptionLevel,
            $strExceptionInfo);
        self::$exceptions[] = $exception;
    }

    /**
     * get exception info
     * @param bool $boolClear
     * @return array[]
     */
    public static function getExceptionInfo($boolClear = true)
    {
        $arrRet = [];
        foreach (self::$exceptions as $exception) {
            $arrRet[] = $exception->getDbArray();
        }
        if ($boolClear) {
            self::clearAllException();
        }
        return $arrRet;
    }

    /**
     * set order id all
     * @param $intOrderId
     */
    public static function setOrderIdAll($intOrderId)
    {
        foreach (self::$exceptions as $exception) {
            $exception->setOrderId($intOrderId);
        }
    }

    /**
     * clear all exception
     */
    public static function clearAllException()
    {
        self::$exceptions = [];
    }
}
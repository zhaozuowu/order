<?php
/**
 * @name Order_BusinessError
 * @desc Error
 * @auth wanggang01@iwaimai.baidu.com
 */
class Order_BusinessError extends Nscm_Exception_Business
{
    /**
     * @param integer $intErrorCode
     * @param string  $strErrorMsg
     * @param array   $arrErrorData
     * @throws Order_BusinessError
     */
    public static function throwException($intErrorCode, $strErrorMsg = '', $arrErrorData = [])
    {
        throw new self($intErrorCode, $strErrorMsg, $arrErrorData);
    }
}

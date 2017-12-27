<?php
/**
 * @name Order_Error
 * @desc Error
 * @auth wanggang01@iwaimai.baidu.com
 */
class Order_Error extends Wm_Error
{
    /**
     * @param integer $intErrorCode
     * @param string  $strErrorMsg
     * @param array   $arrErrorData
     */
    public static function throwException($intErrorCode, $strErrorMsg = '', $arrErrorData = [])
    {
        throw new self($intErrorCode, $strErrorMsg, $arrErrorData);
    }
}

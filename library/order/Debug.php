<?php
/**
 * @name Vendor_Debug
 * @desc debug
 * @auth wanggang01@iwaimai.baidu.com
 */
class Order_Debug
{
    /**
     * debug key from params
     * @var string
     */
    const DEBUG_KEY = 'debug';

    /**
     * break point
     * @param string $strPointName
     * @param array  $arrPointData
     */
    public static function breakPoint($strPointName, $arrPointData)
    {
        if ($_GET[self::DEBUG_KEY] === $strPointName) {
            echo json_encode($arrPointData);
            die();
        }
    }
}

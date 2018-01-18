<?php
/**
 * @name Order_Define_Format
 * @desc
 * @author jinyu02@iwaimai.baidu.com
 */

class Order_Define_Format
{
    /**
     * 格式化设备信息数据
     * @param array $arrDevices
     * @return string
     */
    public static function formatDevices($arrDevices) {
        $strDevices = '';
        if (empty($arrDevices)) {
            return '';
        }
        foreach ((array)$arrDevices as $strKey => $intAmount) {
            $strDevice = $intAmount . '个' . Order_Define_BusinessFormOrder::ORDER_DEVICE_MAP[intval($strKey)];
            $strDevices = $strDevices . $strDevice . '/';
        }
        $strDevices = rtrim($strDevices, '/');
        return $strDevices;
    }
}
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

    /**
     * format stockout info ret
     * @param $arrRet
     * @return array
     */
    public static function formatStockoutInfo($arrRet) {
        $arrFormatRet = [];
        if (empty($arrRet)) {
            return $arrFormatRet;
        }
        $arrFormatRet['stockout_order_id'] = empty($arrRet['stockout_order_id']) ? 0 : $arrRet['stockout_order_id'];
        $arrFormatRet['business_form_order_id'] = intval($arrRet['business_form_order_id']);
        $arrFormatRet['skus'] = self::formatStockoutSkus($arrRet['skus']);
        return $arrFormatRet;
    }

    /**
     * format stockout skus ret
     * @param $arrSkus
     * @return array
     */
    public static function formatStockoutSkus($arrSkus) {
        $arrFormatSkus = [];
        if (empty($arrSkus)) {
            return $arrFormatSkus;
        }
        foreach ((array)$arrSkus as $arrSkuItem) {
            $arrFormatSkuItem = [];
            $arrFormatSkuItem['sku_id'] = empty($arrSkuItem['sku_id']) ? 0 : $arrSkuItem['sku_id'];
            $arrFormatSkuItem['cost_price_tax'] = empty($arrSkuItem['cost_price_tax']) ?
                0 : $arrSkuItem['cost_price_tax'];
            $arrFormatSkuItem['cost_price_untax'] = empty($arrSkuItem['cost_price']) ?
                0 : $arrSkuItem['cost_price'];
            $arrFormatSkuItem['order_amount'] = empty($arrSkuItem['order_amount']) ?
                0 : $arrSkuItem['order_amount'];
            $arrFormatSkuItem['distribute_amount'] = empty($arrSkuItem['distribute_amount']) ?
                0 : $arrSkuItem['distribute_amount'];
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;
    }
}
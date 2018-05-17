<?php
/**
 * @name Order_Util_Sku
 * @author wende.chen@ele.me
 */
class Order_Util_Sku
{
    /**
     * 判断给定的id是否符合upc_id条件
     * @param $strId
     * @return bool
     */
    public static function isUpcId($strId)
    {
        if (empty($strId)) {
            return false;
        }

        if ((Order_Define_Sku::SKU_UPC_ID_MIN_LENGTH <= strlen($strId))
            && (Order_Define_Sku::SKU_UPC_ID_MAX_LENGTH >= strlen($strId))) {
            return true;
        }

        return false;
    }

    /**
     * 判断给定的id是否符合sku_id条件
     * @param $strId
     * @return bool
     */
    public static function isSkuId($strId)
    {
        if (empty($strId)) {
            return false;
        }

        if (Order_Define_Sku::SKU_ID_LENGTH == strlen($strId)) {
            return true;
        }

        return false;
    }
}
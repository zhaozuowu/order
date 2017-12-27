<?php
/**
 * @name Order_Util_Util
 * @desc Order_Util_Util
 * @author lvbochao@iwaimai.baidu.com
 */
class Order_Util_Util
{

    /**
     * generate purchase order code
     * @return int
     */
    public static function generatePurchaseOrderCode()
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
}
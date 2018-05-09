<?php
/**
 * @name Order_Util_Stock
 * @author ziliang.zhang02@ele.me
 */
class Order_Util_Stock
{

    /**
     * 根据商品效期类型，计算生产日期和有效期
     * 计算结果返回到$arrDetail['production_time'] 和 $arrDetail['expire_time']
     * @param $arrDetail
     * @param $intSkuEffectType
     * @param $intSkuEffectDay
     * @return mixed
     * @throws Order_BusinessError
     */
    public static function getEffectTime($arrDetail, $intSkuEffectType, $intSkuEffectDay)
    {
        if(empty($intSkuEffectType)) {
            Bd_Log::warning('sku effect type is empty ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        $intSkuEffectType = intval($intSkuEffectType);

        // 如果是生产日期型的，有效期天数必传
        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            if(!is_numeric($intSkuEffectDay) || $intSkuEffectDay < 0) {
                Bd_Log::warning('sku effect day invalid ' . $intSkuEffectDay);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
            }
        }

        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            // 生产日期型
            $arrDetail['production_time'] = $arrDetail['production_or_expire_time'];
            $arrDetail['expire_time'] = $arrDetail['production_or_expire_time'] + $intSkuEffectDay * 3600 * 24 - 1;
        } else if(Nscm_Define_Sku::SKU_EFFECT_TO === $intSkuEffectType) {
            // 到效期型
            $arrDetail['production_time'] = '';
            $arrDetail['expire_time'] = $arrDetail['production_or_expire_time'] + 3600 * 24 - 1;
        } else {
            Bd_Log::warning('sku effect type invalid ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        return $arrDetail;
    }

    /**
     * 计算到效期
     * @param $intProductionOrExpireTime
     * @param $intSkuEffectType
     * @param $intSkuEffectDay
     * @return float|int
     * @throws Order_BusinessError
     */
    public static function getExpireTime($intProductionOrExpireTime, $intSkuEffectType, $intSkuEffectDay)
    {
        if(empty($intSkuEffectType)) {
            Bd_Log::warning('sku effect type is empty ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        $intSkuEffectType = intval($intSkuEffectType);

        // 如果是生产日期型的，有效期天数必传
        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            if(!is_numeric($intSkuEffectDay) || $intSkuEffectDay < 0) {
                Bd_Log::warning('sku effect day invalid ' . $intSkuEffectDay);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
            }
        }

        $intExpireTime = 0;
        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            // 生产日期型
            $intExpireTime = $intProductionOrExpireTime + $intSkuEffectDay * 3600 * 24 - 1;
        } else if(Nscm_Define_Sku::SKU_EFFECT_TO === $intSkuEffectType) {
            // 到效期型
            $intExpireTime = $intProductionOrExpireTime + 3600 * 24 - 1;
        } else {
            Bd_Log::warning('sku effect type invalid ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        return $intExpireTime;
    }

    /**
     * 调用库存前format到效期类型参数，生产日期型无需format
     * @param $intProductionOrExpireTime
     * @return float|int
     */
    public static function formatExpireTime($intProductionOrExpireTime)
    {
        return $intProductionOrExpireTime + 3600 * 24 - 1;
    }

    /**
     * 计算返回前端的产效期
     * @param $intSkuEffectType
     * @param $intProductionTime
     * @param $intExpirationTime
     * @return false|int
     */
    public static function calculateProductionOrExpirationTime($intSkuEffectType, $intProductionTime, $intExpirationTime) {
        $intProductionTimeOrExpirationTime = 0;
        if (Nscm_Define_Sku::SKU_EFFECT_FROM == $intSkuEffectType) {
            $intProductionTimeOrExpirationTime = strtotime(date('Y-m-d', $intProductionTime));
        } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
            $intProductionTimeOrExpirationTime = strtotime(date('Y-m-d', $intExpirationTime));
        }
        return $intProductionTimeOrExpirationTime;
    }
}
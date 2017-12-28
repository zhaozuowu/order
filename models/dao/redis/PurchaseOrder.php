<?php
/**
 * @name Dao_Redis_PurchaseOrder
 * @desc Dao_Redis_PurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Dao_Redis_PurchaseOrder extends Order_Base_Redis
{
    /**
     * reserve order key prefix
     * @var string
     */
    const KEY_PREFIX = 'nwms:order:purchaseinfo:';

    /**
     * set reserve order info
     * @param $arrPurchaseInfo
     * @return string
     */
    public function setOrderInfo($arrPurchaseInfo)
    {
        $strPurchaseInfo = json_encode($arrPurchaseInfo);
        $strKey = $arrPurchaseInfo['nscm_purchase_order_id'];
        $strRedisKey = self::KEY_PREFIX . $strKey;
        Bd_Log::debug(sprintf('set redis, key[%s], data:`%s`', $strRedisKey, $strPurchaseInfo));
        $boolRes = $this->objRedisConn->set($strRedisKey, $strPurchaseInfo);
        Bd_Log::debug('set redis result: ' . json_encode($boolRes));
        return $strKey;
    }

    /**
     * get order by key
     * @param $strKey
     * @return mixed
     */
    public function getOrderInfo($strKey)
    {
        $strRedisKey = self::KEY_PREFIX . $strKey;
        Bd_Log::debug(sprintf('get from redis, key[%s]', $strRedisKey));
        $strInfo = $this->objRedisConn->get($strRedisKey);
        Bd_Log::debug(sprintf('get from redis, result: `%s`', $strInfo));
        $arrRet = json_decode($strInfo, true);
        return $arrRet;
    }

    /**
     * drop order info
     * @param $strKey
     * @return int
     */
    public function dropOrderInfo($strKey)
    {
        $strRedisKey = self::KEY_PREFIX . $strKey;
        Bd_Log::debug(sprintf('drop from redis, key[%s]', $strRedisKey));
        $intRet = $this->objRedisConn->del($strRedisKey);
        Bd_Log::debug(sprintf('drop from redis, result: `%s`', $intRet));
        return $intRet;
    }
}
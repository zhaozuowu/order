<?php
/**
 * @name Dao_Redis_ReserveOrder
 * @desc Dao_Redis_ReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Dao_Redis_ReserveOrder extends Order_Base_Redis
{
    /**
     * reserve order key prefix
     * @var string
     */
    const KEY_PREFIX = 'nwms:order:reserveinfo:';

    /**
     * set reserve order info
     * @param $arrReserveInfo
     * @return string
     */
    public function setOrderInfo($arrReserveInfo)
    {
        $strReserveInfo = json_encode($arrReserveInfo);
        $strKey = $arrReserveInfo['purchase_order_id'];
        $strRedisKey = self::KEY_PREFIX . $strKey;
        Bd_Log::debug(sprintf('set redis, key[%s], data:`%s`', $strRedisKey, $strReserveInfo));
        $boolRes = $this->objRedisConn->set($strRedisKey, $strReserveInfo);
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
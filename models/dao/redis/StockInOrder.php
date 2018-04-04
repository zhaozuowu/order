<?php
/**
 * @name Dao_Redis_StockInOrder
 * @desc Dao_Redis_StockInOrder
 * @author hang.song02@ele.me
 */

class Dao_Redis_StockInOrder extends Order_Base_Redis
{
    /**
     * stockin order id key prefix
     * @var string
     */
    const REDIS_STOCKIN_ORDER_ID_KEY_PREFIX = 'nwms:order:stockin:id:';

    /**
     * stockin order id key expire time
     * @var integer
     */
    const EXPIRE_TIME = 5;

    /**
     * set stockin order id key
     * @param  integer  $intSourceOrderId
     * @param  integer  $intStockInOrderId
     * @return void
     */
    public function setStockInOrderId($intSourceOrderId, $intStockInOrderId) {
        $strRedisKey = self::REDIS_STOCKIN_ORDER_ID_KEY_PREFIX . strval($intSourceOrderId);
        Bd_Log::debug(sprintf('set redis, key[%s], data:%s', $strRedisKey, $intStockInOrderId));
        $boolRes = $this->objRedisConn->set($strRedisKey, $intStockInOrderId);
        Bd_Log::debug('set redis result: ' . json_encode($boolRes));
        $this->objRedisConn->expire($strRedisKey, self::EXPIRE_TIME);
    }

    /**
     * get val by source order id
     * @param string $intSourceOrderId
     * @return integer
     */
    public function getValBySourceOrderId($intSourceOrderId) {
        $strRedisKey = self::REDIS_STOCKIN_ORDER_ID_KEY_PREFIX . strval($intSourceOrderId);
        Bd_Log::debug(sprintf('get from redis, key[%s]', $strRedisKey));
        $intStockInOrderId = intval($this->objRedisConn->get($strRedisKey));
        Bd_Log::debug(sprintf('get from redis, result: `%s`', $intStockInOrderId));
        return $intStockInOrderId;
    }
}
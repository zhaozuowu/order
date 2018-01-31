<?php
/**
 * @name Dao_Redis_StockoutOrder
 * @desc Dao_Redis_StockoutOrder
 * @author jinyu02@iwaimai.baidu.com
 */

class Dao_Redis_StockoutOrder extends Order_Base_Redis
{
    /**
     * stockout order id key prefix
     * @var string
     */
    const REDIS_STOCKOUT_CUSTOMER_ID_KEY_PREFIX = 'nwms:order:stockout:customerid:';

    /**
     * stockout order id key expire time
     * @var integer
     */
    const EXPIRE_TIME = 5;

    /**
     * set stockout customer id key
     * @param array $strCustomerId
     * @return void
     */
    public function setCustomerId($strCustomerId) {
        $strRedisKey = self::REDIS_STOCKOUT_CUSTOMER_ID_KEY_PREFIX . $strCustomerId;
        $this->objRedisConn->incr($strRedisKey);
        $this->objRedisConn->expire($strRedisKey, self::EXPIRE_TIME);
    }

    /**
     * get val by customer id
     * @param string $strCustomerId
     * @return integer
     */
    public function getValByCustomerId($strCustomerId) {
        $strRedisKey = self::REDIS_STOCKOUT_CUSTOMER_ID_KEY_PREFIX . $strCustomerId;
        return $this->objRedisConn->get($strRedisKey);
    }
}
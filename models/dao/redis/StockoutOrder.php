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
    const REDIS_STOCKOUT_ORDER_ID_KEY_PREFIX = 'nwms:order:stockoutorderid:';

    /**
     * stockout order id key expire time
     * @var integer
     */
    const EXPIRE_TIME = 120;

    /**
     * set stockout order id key
     * @param integer $intStockoutOrderId
     * @return void
     */
    public function setOrderId($intStockoutOrderId) {
        $strRedisKey = self::REDIS_STOCKOUT_ORDER_ID_KEY_PREFIX . $intStockoutOrderId;
        $this->objRedisConn->incr($strRedisKey);
        $this->objRedisConn->expire($strRedisKey, self::EXPIRE_TIME);
    }

    /**
     * get val by order id
     * @param $intStockoutOrderId
     */
    public function getValByOrderId($intStockoutOrderId) {
        $strRedisKey = self::REDIS_STOCKOUT_ORDER_ID_KEY_PREFIX . $intStockoutOrderId;
        return $this->objRedisConn->get($strRedisKey);
    }
}
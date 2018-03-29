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

    const REDIS_LOGISTICS_STOCKOUT_INFO = 'nwms:order:stockout:logisticsorderid:';

    /**
     * stockout order id key expire time
     * @var integer
     */
    const EXPIRE_TIME = 5;

    /**
     * stockout info cache expire time
     * @var integer
     */
    const FORMAT_RET_EXPIRE_TIME = 3600;

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

    /**
     * set stockout info cache
     * @param $strLogisticsOrderId
     * @param $arrFormatRet
     * @return bool
     */
    public function setCacheStockoutInfo($intLogisticsOrderId, $arrFormatRet) {
        $strRedisKey = self::REDIS_LOGISTICS_STOCKOUT_INFO . $intLogisticsOrderId;
        $strFormatRet = json_encode($arrFormatRet);
        return $this->objRedisConn->setex($strRedisKey, self::FORMAT_RET_EXPIRE_TIME, $strFormatRet);
    }

    /**
     * get stockout info cache
     * @param $intLogisticsOrderId
     * @return array
     */
    public function getCacheStockoutInfoByLogisticsOrderId($intLogisticsOrderId) {
        $strRedisKey = self::REDIS_LOGISTICS_STOCKOUT_INFO . $intLogisticsOrderId;
        $strRet = $this->objRedisConn->get($strRedisKey);
        $arrRet = json_decode($strRet, true);
        return $arrRet;
    }
}
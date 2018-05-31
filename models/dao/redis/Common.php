<?php
/**
 * @name Dao_Redis_Common
 * @desc Dao_Redis_Common
 * @author hang.song02@ele.me
 */
class Dao_Redis_Common extends Order_Base_Redis
{
    /**
     * reserve order key prefix
     * @var string
     */
    const KEY_PREFIX = 'nscm_order_stock_price_fix';

    public function setNeedFixWarehouseSkuList($arrWarehouseSkuList)
    {
        $strWarehouseSku = json_encode($arrWarehouseSkuList);
        Bd_Log::debug(sprintf('set redis, key[%s], data:%s', self::KEY_PREFIX, $strWarehouseSku));
        $boolRes = $this->objRedisConn->set(self::KEY_PREFIX, $strWarehouseSku);
        Bd_Log::debug('set redis result: ' . json_encode($boolRes));
        if (empty($boolRes)) {
            Order_BusinessError::throwException(Order_Error_Code::CONNECT_REDIS_FAILED);
        }
        return self::KEY_PREFIX;
    }

    /**
     * @return array
     */
    public function getNeedFixWarehouseSkuList()
    {
        $arrRet = $this->objRedisConn->get(self::KEY_PREFIX);
        if (empty($arrRet)) {
            return [];
        }
        return json_decode($arrRet, true);
    }
}
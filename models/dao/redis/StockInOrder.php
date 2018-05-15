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

    /**
     * get operate record
     * @param $strOrderId
     * @return array
     */
    public function getOperateRecord($strOrderId)
    {
        $strRedisKey = Nscm_Define_RedisPrefix::NWMS_STOCKIN_OPERATE_RECORD . $strOrderId;
        Bd_Log::debug(sprintf('get from redis, key[%s]', $strRedisKey));
        $strOrderInfo = $this->objRedisConn->get($strRedisKey);
        Bd_Log::debug(sprintf('get from redis, result: `%s`', json_encode($strOrderInfo)));
        $arrRet = [];
        if (empty($strOrderInfo)) {
            Bd_Log::trace('redis empty, return []');
        } else {
            $arrRet = Nscm_Lib_Util::jsonDecode($strOrderInfo, true);
        }
        return $arrRet;
    }

    /**
     * @param $strOrderId
     * @param $strOperateName
     * @param $strOperateDevice
     * @param $intOperateTime
     * @return bool
     * @throws Order_BusinessError
     */
    public function addOperateRecord($strOrderId, $strOperateName, $strOperateDevice, $intOperateTime)
    {
        $strRedisKey = Nscm_Define_RedisPrefix::NWMS_STOCKIN_OPERATE_RECORD . $strOrderId;
        $arrAllRecords = $this->getOperateRecord($strOrderId);
        $arrNewRecord = [
            'operate_time' => intval($intOperateTime),
            'operate_name' => strval($strOperateName),
            'operate_device' => strval($strOperateDevice),
        ];
        $arrAllRecords[] = $arrNewRecord;
        $strContent = json_encode($arrAllRecords);
        Bd_Log::debug(sprintf('set redis, key[%s], data:%s', $strRedisKey, $strContent));
        $boolRes = $this->objRedisConn->set($strRedisKey, $strContent);
        Bd_Log::debug('set redis result: ' . json_encode($boolRes));
        if (false === $boolRes) {
            Bd_Log::warning('set redis fail');
            Order_BusinessError::throwException(Order_Error_Code::RAL_ERROR);
        }
        return $boolRes;

    }

    /**
     * @param $strOrderId
     * @return int
     */
    public function dropOperateRecord($strOrderId) {
        $strRedisKey = Nscm_Define_RedisPrefix::NWMS_STOCKIN_OPERATE_RECORD . $strOrderId;
        Bd_Log::debug(sprintf('drop redis, key[%s]', $strRedisKey));
        $boolRes = $this->objRedisConn->delete($strRedisKey);
        Bd_Log::debug('drop redis result: ' . json_encode($boolRes));
        if (false == $boolRes) {
            Bd_Log::warning('drop redis key fail: ' . $strRedisKey);
        }
        return $boolRes;
    }
}
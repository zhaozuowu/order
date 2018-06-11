<?php
/**
 * @name SyncInbound.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/6/7 20:19
 */

class Dao_Huskar_SyncInbound
{

    /**
     * @var Nscm_Lib_ApiHuskar
     */
    protected static $objHuskar;

    const NSCM_INBOUND_DIRECT = 'SyncInbound';

    /**
     * sync inbound direct
     * @param $intInboundId
     * @param $intStatus
     * @param $intActualTime
     * @param $arrItems
     * @return mixed
     * @throws Nscm_Exception_Error
     */
    public static function syncInboundDirect($intInboundId, $intStatus, $intActualTime, $arrItems)
    {
        if (empty(self::$objRal)) {
            self::$objHuskar = new Nscm_Lib_ApiHuskar();
        }
        $req = [
            self::NSCM_INBOUND_DIRECT => [
                'inbound_id' => $intInboundId,
                'status' => $intStatus,
                'actual_time' => $intActualTime,
                'items' => $arrItems,
            ],
        ];
        Bd_Log::debug('ral sync inbound direct input params: ' . json_encode($req));
        $ret = self::$objHuskar->getData($req);
        Bd_Log::debug('ral sync inbound direct out params: ' . json_encode($ret));
        return $ret[self::NSCM_INBOUND_DIRECT];
    }
}
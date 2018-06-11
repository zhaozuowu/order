<?php
/**
 * @name Dao_Ral_SyncInbound
 * @desc 同步彩云系统
 * @author lvbochao@iwaimai.baidu.com
 */

class Dao_Ral_SyncInbound
{

    /**
     * @var Order_ApiRaler $objRal
     */
    private static $objRal;

    /**
     * nscm inbound direct
     * @var string
     */
    const NSCM_INBOUND_DIRECT = 'nscminbouddirect';

    /**
     * sync inbound direct
     * @param $intInboundId
     * @param $intStatus
     * @param $intActualTime
     * @param $arrItems
     * @return mixed
     * @throws Nscm_Exception_Error
     * @deprecated
     */
    public static function syncInboundDirect($intInboundId, $intStatus, $intActualTime, $arrItems)
    {
        if (empty(self::$objRal)) {
            self::$objRal = new Order_ApiRaler();
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
        $ret = self::$objRal->getData($req);
        Bd_Log::debug('ral sync inbound direct out params: ' . json_encode($ret));
        return $ret[self::NSCM_INBOUND_DIRECT];
    }

    /**
     * sync inbound self
     * @param $intInboundId
     * @param $intStatus
     * @param $intActualTime
     * @param $arrItems
     */
    public static function syncInboundSelf($intInboundId, $intStatus, $intActualTime, $arrItems)
    {
        $arrInput = [
            'inbound_id' => strval($intInboundId),
            'status' => strval($intStatus),
            'actual_time' => strval($intActualTime),
            'items' => $arrItems,
        ];
        Bd_Log::trace('send wmq cmd sync inbound self, req: ' . json_encode($arrInput));
        Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_SYNC_INBOUND_NWMS, $arrInput,
            strval($intInboundId));
    }
}
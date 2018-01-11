<?php
/**
 * @name Dao_Ral_SyncInbound
 * @desc 同步彩云系统
 * @author lvbochao@iwaimai.baidu.com
 */

class Dao_Ral_SyncInbound
{

    /**
     * @param $intInboundId
     * @param $intStatus
     * @param $intActualTime
     * @param $arrItems
     */
    public static function syncInbound($intInboundId, $intStatus, $intActualTime, $arrItems)
    {
        $arrInput = [
            'inbound_id' => $intInboundId,
            'status' => $intStatus,
            'actual_time' => $intActualTime,
            'items' => $arrItems,
        ];
        Bd_Log::trace('send wmq cmd sync inbound, req: ' . json_encode($arrInput));
        Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_SYNC_INBOUND, $arrInput,
            strval($intInboundId), Order_Define_Cmd::NSCM_SYNC_INBOUND);
    }
}
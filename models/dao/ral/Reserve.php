<?php
/**
 * @name Dao_Ral_Reserve
 * @desc 预约单
 * @author lvbochao@iwaimai.baidu.com
 */

class Dao_Ral_Reserve
{
    /**
     * write db
     * @param int $intPurchaseOrderId <p>
     * 单号
     * </p>
     * @return bool
     */
    public static function writeReserveOrderDb($intPurchaseOrderId)
    {
        $arrInput = [
            'purchase_order_id' => strval($intPurchaseOrderId),
        ];
        Bd_Log::trace('send wmq cmd, req: ' . json_encode($arrInput));
        Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_RESERVE_ORDER, $arrInput,
            strval($intPurchaseOrderId), Order_Define_Cmd::NWMS_ORDER_TOPIC);
        return true;
    }
}
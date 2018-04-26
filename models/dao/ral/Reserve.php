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
     * @throws Order_BusinessError
     */
    public static function writeReserveOrderDb($intPurchaseOrderId)
    {
        $arrInput = [
            'purchase_order_id' => strval($intPurchaseOrderId),
        ];
        Bd_Log::trace('send wmq cmd, req: ' . json_encode($arrInput));
        $boolRet = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_RESERVE_ORDER, $arrInput,
            strval($intPurchaseOrderId), Order_Define_Cmd::NWMS_ORDER_TOPIC);
        if (false == $boolRet) {
            Bd_Log::warning('write wmq failed!');
            Order_BusinessError::throwException(Order_Error_Code::RESERVE_STOCKIN_SEND_WMQ_FAIL);
        }
        return true;
    }
}
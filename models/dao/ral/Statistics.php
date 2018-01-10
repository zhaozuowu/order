<?php
/**
 * @name Dao_Ral_Statistics
 * @desc 统计调用
 * @author lvbochao@iwaimai.baidu.com
 */

class Dao_Ral_Statistics
{

    /**
     * @param int $intTable <p>
     * table：表，1-采购入库，2-销退入库，3-出库
     * </p>
     * @param int $intType <p>
     * type: 类型，1-新增，2-修改
     * </p>
     * @param $intKey <p>
     * key：主键，即单号，不包含字母
     * </p>
     * @return bool
     */
    public static function syncStatistics($intTable, $intType, $intKey)
    {
        if (!isset(Order_Statistics_Type::TABLE_MAP[$intTable])) {
            Bd_Log::warning('sync statistics table params error! input: ' . $intTable);
            return false;
        }
        if (!isset(Order_Statistics_Type::OPERATE_MAP[$intType])) {
            Bd_Log::warning('sync statistics type params error! input: ' . $intType);
            return false;
        }
        $arrInput = [
            'type' => $intType,
            'table' => $intTable,
            'key' => intval($intKey),
        ];
        Bd_Log::trace('send wmq cmd, req: ' . json_encode($arrInput));
        Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_SYNC_FORM_STATISTICS, $arrInput,
            strval($intType . $intKey), Order_Define_Cmd::NWMS_ORDER_TOPIC);
        return true;
    }
}
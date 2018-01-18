<?php
/**
 * @name Dao_Ral_Log
 * @desc dao for ral log
 * @author wanggang(wanggang01@iwaimai.baidu.com)
 */

class Dao_Ral_Log
{
    /**
     * get log list api
     * @var string
     */
    const API_RALER_GET_LOG_LIST = 'getloglist';

    /**
     * log type sku
     * @var integer
     */
    const LOG_TYPE_SKU = 1;

    /**
     * log type order
     * @var integer
     */
    const LOG_TYPE_VENDOR = 2;

    /**
     * log type quotation
     * @var integer
     */
    const LOG_TYPE_QUOTATION = 3;

    /**
     * create
     * @var integer
     */
    const LOG_OPERATION_TYPE_CREATE = 1;

    /**
     * update
     * @var integer
     */
    const LOG_OPERATION_TYPE_UPDATE = 2;

    /**
     * delete
     * @var integer
     */
    const LOG_OPERATION_TYPE_DELETE = 3;

    /**
     * get log list
     * @param  integer $intLogType
     * @param  string  $strContentKey
     * @param  integer $intPageSize
     * @param  integer $intPageNum
     * @return string
     */
    public function getGetLogListParams($intLogType, $strContentKey, $intPageSize = 20, $intPageNum = 1)
    {
        $req = [
            'app_id' => Nscm_Define_App::APP_VENDOR,
            'log_type' => intval($intLogType),
            'page_size' => intval($intPageSize),
            'page_num' => intval($intPageNum),
        ];
        if (!empty($strContentKey)) {
            $req['quota_idx_int_1'] = intval($strContentKey);
        }
        return $req;
    }

    /**
     * get log list
     * @param  integer $intLogType
     * @param  string  $strContentKey
     * @param  integer $intPageSize
     * @param  integer $intPageNum
     * @return array
     */
    public function getLogList($intLogType, $strContentKey = '', $intPageSize = 20, $intPageNum = 1)
    {
        $req = $this->getGetLogListParams($intLogType, $strContentKey, $intPageSize, $intPageNum);
        $ret = Nscm_Service_OperationLog::getLogList($req);
        Vendor_Debug::breakPoint(self::API_RALER_GET_LOG_LIST, [
            'req' => $req,
            'ret' => $ret,
        ]);
        if (empty($ret)) {
            $ret = [];
        }
        return $ret;
    }

    /**
     * write log
     * @param $logType
     * @param $quotaIdxInt1
     * @param $operationType
     * @param string $operatorName
     * @param int $operatorId
     * @param string $content
     * @return array|bool
     */
    public function addLog($logType, $quotaIdxInt1, $operationType, $operatorName='', $operatorId=0, $content='')
    {
        $ret = [];
        if (empty($logType) || empty($quotaIdxInt1) || empty($operationType) || empty($operatorName) || empty($operatorId)) {
            return $ret;
        }
        $appId = Order_Define_StockoutOrder::APP_NWMS_ORDER_APP_ID;
        Nscm_Event_ShutDownEvent::register();
        $list = Nscm_Event_ShutDownEvent::add(['Nscm_Service_OperationLog', 'addLog'], $appId, $logType, $operationType, $operatorName, $operatorId, $content,$quotaIdxInt1);   //写入的日志内容
        return $list;

    }
}

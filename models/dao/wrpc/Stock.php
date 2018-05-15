<?php
/**
 * @name Dao_Wrpc_Stock
 * @desc interact with stock
 * @author hang.song02@ele.me
 */
class Dao_Wrpc_Stock
{
    /**
     * wrcp service
     * @var Bd_Wrpc_Client
     */
    private $objWrpcService;

    /**
     * init
     * @param string $strServiceName 请求服务的service
     */
    public function __construct($strServiceName = Order_Define_Wrpc::NWMS_STOCK_SERVICE_NAME)
    {
        $this->objWrpcService = new Bd_Wrpc_Client(Order_Define_Wrpc::NWMS_APP_ID,
                                                    Order_Define_Wrpc::NWMS_STOCK_NAMESPACE,
                                                    $strServiceName);
    }

    /**
     * 获取sku库区库位信息
     * @param $intWarehouseId
     * @param $intSkuId
     * @param $strLocationCode
     * @param $strAreaCode
     * @param $strTimeParam
     * @param $intExpireTime
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getSkuLocation($intWarehouseId, $intSkuId, $strLocationCode, $strAreaCode, $strTimeParam, $intExpireTime)
    {
        $arrReqParams['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'sku_ids' => strval($intSkuId),
            $strTimeParam => strtotime(date("Y-m-d H:i:s",$intExpireTime)),
            'location_code' => $strLocationCode,
            'area_code' => $strAreaCode,
        ];
        Bd_Log::trace(sprintf("method[%s] get_sku_location_request[%d]",
            __METHOD__, json_encode($arrReqParams)));
        $arrRet = $this->objWrpcService->getPickableSkuBatchInfo($arrReqParams);
        Bd_Log::trace(sprintf("method[%s] get_sku_location_ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] routing-key[%s]",
                __METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_CREATE_SHIPMENTORDER_ERROR);
        }
        return $arrRet['data'];
    }

}
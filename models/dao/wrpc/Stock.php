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
     * @param $strTimeParam
     * @param $intExpireTime
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getSkuLocation($intWarehouseId, $intSkuId, $strLocationCode, $strTimeParam, $intExpireTime)
    {
        $arrReqParams['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'sku_ids' => strval($intSkuId),
        ];
        if (!empty($intExpireTime)) {
            $arrReqParams['requestParams'][$strTimeParam] = strtotime(date("Y-m-d H:i:s",$intExpireTime));
        }
        if (!empty($strLocationCode)) {
            $arrReqParams['requestParams']['location_code'] = $strLocationCode;
        }
        Bd_Log::trace(sprintf("method[%s] get_sku_location_request[%d]",
            __METHOD__, json_encode($arrReqParams)));
        $arrRet = $this->objWrpcService->getPickableSkuBatchInfo($arrReqParams);
        Bd_Log::trace(sprintf("method[%s] get_sku_location_ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] routing-key[%s]",
                __METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::GET_SKU_STOCK_INFO_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 拣货完成通知仓库转移库存
     * @param $intPickupOrderId
     * @param $intWarehouseId
     * @param $arrPickupSkus
     * @return mixed
     * @throws Order_BusinessError
     */
    public function pickStock($intPickupOrderId, $intWarehouseId, $arrPickupSkus)
    {
        $arrReqParams['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'ext_order_id' => strval($intPickupOrderId),
            'details' => $arrPickupSkus,
        ];
        Bd_Log::trace(sprintf("method[%s] finish_pickup_notify_stock_request[%d]",
            __METHOD__, json_encode($arrReqParams)));
        $arrRet = $this->objWrpcService->pickStock($arrReqParams);
        Bd_Log::trace(sprintf("method[%s] finish_pickup_notify_stock_ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] ret[%s]",
                __METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::FINISH_PICKUP_ORDER_NOTIFY_STOCK_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 作废拣货单通知库存
     * @param $intPickupOrderId
     * @param $intWarehouseId
     * @return mixed
     * @throws Order_BusinessError
     */
    public function cancelStockLocRecommend($intPickupOrderId, $intWarehouseId)
    {
        $arrReqParams['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'ext_order_id' => strval($intPickupOrderId),
        ];
        Bd_Log::trace(sprintf("method[%s] cancel_pickup_notify_stock_request[%d]",
            __METHOD__, json_encode($arrReqParams)));
        $arrRet = $this->objWrpcService->cancelStockLocRecommend($arrReqParams);
        Bd_Log::trace(sprintf("method[%s] cancel_pickup_notify_stock_ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] ret[%s]",
                __METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::CANCEL_PICKUP_ORDER_NOTIFY_STOCK_FAIL);
        }
        return $arrRet['data'];
    }

}
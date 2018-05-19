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
        Bd_Log::trace(sprintf("method[%s] finish_pickup_notify_stock_request[%s]",
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

    /**
     * 确认上架单
     * @param $intPlaceOrderId
     * @param $intWarehouseId
     * @param $arrSkusPlace
     * @throws Order_BusinessError
     */
    public function confirmLocation($intPlaceOrderId, $intWarehouseId, $intIsDefective, $arrSkusPlace)
    {
        $arrParams = [];
        $arrRequestParams['p_order_id'] = $intPlaceOrderId;
        $arrRequestParams['warehouse_id'] = $intWarehouseId;
        $arrRequestParams['details'] = $this->getLocationDetails($arrSkusPlace, $intIsDefective);
        $arrParams['requestParams'] = $arrRequestParams;
        $arrRet = $this->objWrpcService->confirmLocation($arrParams);
        Bd_Log::trace(sprintf("method[%s] params[%s] ret[%s]",
                __METHOD__, json_encode($arrParams), json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("confirm place order failed params[%s] ret[%s]",
                            json_encode($arrParams), json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NOTIFY_STOCK_PLACE_ORDER_CONFIRM_FAILE);
        }
    }

    /**
     * 获取库位参数详情
     * @param $arrSkusPlace
     * @param $intIsDefective
     * @return array
     */
    protected function getLocationDetails($arrSkusPlace, $intIsDefective)
    {
        $arrLocationDetails = [];
        foreach ((array)$arrSkusPlace as $arrSkusPlaceItem) {
            $arrLocationDetailItem = [];
            $arrLocationDetailItem['sku_id'] = $arrSkusPlaceItem['sku_id'];
            $arrLocationDetailItem['expiration_time'] = intval($arrSkusPlaceItem['expire_date']);
            $arrLocationDetailItem['is_defective'] = $intIsDefective;
            $arrLocationDetailItem['target_details'] = $this->getTargetDetails($arrSkusPlaceItem['actual_info']);
            if (!empty($arrLocationDetailItem['target_details'])) {
                $arrLocationDetails[] = $arrLocationDetailItem;
            }
        }
        return $arrLocationDetails;
    }

    /**
     * 拼接实际上架数量信息
     * @param $arrActualInfo
     * @return array
     */
    protected function getTargetDetails($arrActualInfo)
    {
        $arrTargetDetails = [];
        foreach ((array)$arrActualInfo as $arrActualInfoItem) {
            $arrTargetDetailItem = [];
            $arrTargetDetailItem['amount'] = $arrActualInfoItem['place_amount'];
            $arrLocation = explode('-', $arrActualInfoItem['place_location_id']);
            $arrTargetDetailItem['target_location_code'] = $arrLocation[0];
            $arrTargetDetailItem['target_area_code'] = $arrLocation[1];
            $arrTargetDetailItem['target_roadway_code'] = $arrLocation[2];
            $arrTargetDetails[] = $arrTargetDetailItem;
        }
        return $arrTargetDetails;
    }

    /**
     * 拣货完成释放库存
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $arrStockoutDetail
     * @return bool
     * @throws Order_BusinessError
     */
    public function unfreezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrStockoutDetail)
    {
        $arrRequestParams['stockout_order_id'] = $intStockoutOrderId;
        $arrRequestParams['warehouse_id'] = $intWarehouseId;
        $arrRequestParams['stockout_details'] = $arrStockoutDetail;
        $arrParams['requestParams'] = $arrRequestParams;
        $arrRet = $this->objWrpcService->deliverStock($arrParams);
        Bd_Log::trace(sprintf("method[%s] params[%s] ret[%s]",
            __METHOD__, json_encode($arrParams), json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("unfreeze sku stock failed params[%s] ret[%s]",
                json_encode($arrParams), json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL);
        }
        return true;
    }

    /**
     * 冻结库存
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $arrFreezeStockDetail
     * @return mixed
     * @throws Order_BusinessError
     */
    public function freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail)
    {
        $arrRequestParams['stockout_order_id'] = $intStockoutOrderId;
        $arrRequestParams['warehouse_id'] = $intWarehouseId;
        $arrRequestParams['freeze_details'] = $arrFreezeStockDetail;
        $arrParams['requestParams'] = $arrRequestParams;
        $arrRet = $this->objWrpcService->reserveStock($arrParams);
        Bd_Log::trace(sprintf("method[%s] params[%s] ret[%s]",
            __METHOD__, json_encode($arrParams), json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("reserve sku stock failed params[%s] ret[%s]",
                json_encode($arrParams), json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 取消出库单释放库存
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @return bool
     * @throws Order_BusinessError
     */
    public function cancelFreezeSkuStock($intStockoutOrderId, $intWarehouseId)
    {
        $arrRequestParams['stockout_order_id'] = $intStockoutOrderId;
        $arrRequestParams['warehouse_id'] = $intWarehouseId;
        $arrParams['requestParams'] = $arrRequestParams;
        $arrRet = $this->objWrpcService->cancelReserveStock($arrParams);
        Bd_Log::trace(sprintf("method[%s] params[%s] ret[%s]",
            __METHOD__, json_encode($arrParams), json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("cancel reserve sku stock failed params[%s] ret[%s]",
                json_encode($arrParams), json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * @param $intWarehouseId
     * @param $pickupOrderId
     * @param $details
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getRecommendStockLoc($intWarehouseId, $pickupOrderId, $details)
    {
        Bd_Log::trace(sprintf("method[%s] get_recommend_stock_loc_details_[%d]_pickup_order_id[%d]_details_[%d]",
            __METHOD__,$details, $intWarehouseId, json_encode($details)));

        $param['requestParams']= [
            'ext_order_id'=>$pickupOrderId,
            'warehouse_id'=>$intWarehouseId,
            'details'=> $details,
        ];
        $arrRet = $this->objWrpcService->recommendStockLoc($param);
        print_r($arrRet);exit();
        $arrRet = is_array($arrRet) ? $arrRet:[];
        Bd_Log::trace(sprintf("method[%s] get_recommend_stock_loc_ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s]",
                __METHOD__, json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_GET_RECOMEND_STOCKLOC_FAIL);
        }
        return $arrRet['data'];
    }
}
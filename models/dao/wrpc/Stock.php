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
            $strTimeParam => strtotime(date("Y-m-d H:i:s",$intExpireTime)),
            'location_code' => $strLocationCode,
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

    /**
     * 获取仓库商品可冻结数据
     * @param $intWarehouseId
     * @param $intSkuId
     * @param $intIsDefective
     * @param $intSkuEffectType
     * @param $intTime
     * @param $intPageNum
     * @param $intPageSize
     * @param $intType
     * @return array|mixed
     * @throws Order_BusinessError
     */
    public function getStockFrozenInfo(
        $intWarehouseId,
        $intSkuId,
        $intIsDefective,
        $intSkuEffectType,
        $intTime,
        $intPageNum,
        $intPageSize,
        $intType = Nscm_Define_Stock::FROZEN_TYPE_CREATE_BY_USER
    )
    {
        $arrRequest = [];
        //仓库ID
        if(empty($intWarehouseId)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is empty');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR);
        }
        $arrRequest['warehouse_id'] = $intWarehouseId;

        //商品ID
        if (!empty($intSkuId)) {
            $arrRequest['sku_ids'] = $intSkuId;
        }

        //效期类型与时间
        if (!empty($intSkuEffectType) && empty($intTime) || empty($intSkuEffectType) && !empty($intTime)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is error');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR);
        } else if (!empty($intSkuEffectType) && !empty($intTime)) {
            if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $intTime = Order_Util_Stock::formatExpireTime($intTime);
                $arrRequest['expiration_time'] = $intTime;
            } else {
                $arrRequest['production_time'] = $intTime;
            }
        }

        //质量状态
        if (!empty($intIsDefective)) {
            $arrRequest['is_defective'] = $intIsDefective;
        }

        //冻结类型
        $arrRequest['type'] = $intType;

        //分页
        if (!empty($intPageNum) && !empty($intPageSize)) {
            $arrRequest['page_num'] = $intPageNum;
            $arrRequest['page_size'] = $intPageSize;
        }

        $arrRequest = ['requestParams' => $arrRequest];
        Bd_log::trace(sprintf('get stock frozen info, param:%s', json_encode($arrRequest)));

        $arrRet = $this->objWrpcService->getFreezableSkuBatchInfo($arrRequest);
        Bd_Log::trace(sprintf("method[%s] get stock frozen info ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed, result is empty.' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 获取仓库
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockWarehouse()
    {
        $arrRet = $this->objWrpcService->getStockWarehouse();
        Bd_Log::trace(sprintf("method[%s] get stock warehouse ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning('wrpc call stock model get stock warehouse failed. ret: ' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_GET_STOCK_WAREHOUSE_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 调用库存冻结
     * @param $arrFrozenArg
     * @return mixed
     * @throws Order_BusinessError
     */
    public function frozenStock($arrFrozenArg)
    {
        $arrRet = $this->objWrpcService->freezeStock(['requestParams' => $arrFrozenArg]);
        Bd_Log::trace(sprintf("method[%s]frozen sku ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning('wrpc call stock model frozen sku failed. ret: ' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_FROZEN_SKU_STOCK_FAIL);
        }
        return $arrRet['data'];
    }

    /**
     * 调用库存解冻
     * @param $arrUnfrozenArg
     * @return mixed
     * @throws Order_BusinessError
     */
    public function unfrozenStock($arrUnfrozenArg)
    {
        $arrRet = $this->objWrpcService->unfreezeStock(['requestParams' => $arrUnfrozenArg]);
        Bd_Log::trace(sprintf("method[%s]unfrozen sku ret[%s]",
            __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning('wrpc call stock model unfrozen sku failed. ret:' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_UNFROZEN_SKU_STOCK_FAIL);
        }
        return $arrRet['data'];
    }
}
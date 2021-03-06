<?php

/**
 * @name Dao_Huskar_Stock
 * @desc interact with stock
 * @author yuxing.zhang@ele.me
 */
class Dao_Huskar_Stock
{
    /**
     * huskar
     * @var Nscm_lib_ApiHuskar
     */
    private $objApiHuskar;

    /**
     * 库存调整
     * @var string
     */
    const  API_RALER_ADJUST_SKU_STOCK = 'adjustskustock';

    /**
     * 冻结单——冻结
     * @var string
     */
    const  API_RALER_FROZEN_STOCK = 'frozenstock';

    /**
     * 冻结单——解冻
     * @var string
     */
    const  API_RALER_UNFROZEN_STOCK = 'unfrozenstock';

    /**
     * 冻结单——获取仓库可冻结数据
     * @var string
     */
    const  API_RALER_STOCK_FROZEN_INFO = 'getfreezableskubatchinfo';

    /**
     * 冻结单——获取仓库
     * @var string
     */
    const  API_RALER_GET_STOCK_WAREHOUSE = 'getstockwarehouse';

    /**
     * 作废出库单
     */
    const  API_RALER_CANCEL_FREEZESKU_STOCK = 'cancelfreezeskustock';

    /**
     * 获取仓库某商品当前库存详情
     * @var string
     */
    const  API_RALER_STOCK_DETAIL = 'stockdetail';

    /**
     * 按照效期的方式，获取库存
     */
    const API_RALER_STOCK_PERIOD_DETAIL = 'getadjustableskubatchinfo';

    /**
     * 库存调整-出库
     * @var string
     */
    const  API_RALER_ADJUST_STOCKOUT = 'adjuststockout';

    /**
     * 库存调整-入库
     * @var string
     */
    const  API_RALER_STOCK_IN = 'stockin';

    /**
     * 拣货--获取商品库区库位
     * @var string
     */
    const  API_RALER_GET_SKU_LOCATION = 'getskulocation';

    /**
     * 库位--批量获取库位信息
     * @var string
     */
    const  API_HUSKAR_GET_BATCH_STORAGE_LOCATION = 'getbatchstoragelocation';

    /**
     * 出库单预占sku
     */
    const API_RALER_RESERVE_STOCK = 'reserveStock';

    /**
     * init
     */
    public function __construct()
    {
        $this->objApiHuskar = new Nscm_lib_ApiHuskar();
    }

    /**
     * 批量获取库位信息
     * @param $intWarehouseId
     * @param $arrLocationCodes
     * @return array|mixed
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getBatchStorageLocation($intWarehouseId, $arrLocationCodes)
    {
        $ret = [];

        $req[self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION] = [
            'warehouse_id'   => $intWarehouseId,
            'location_codes' => $arrLocationCodes,
        ];

        Bd_Log::trace('huskar call ' . self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION . ' input params ' . json_encode($req));
        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $ret = $this->objApiHuskar->getData($req);
        $ret = empty($ret[self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION]) ? [] : $ret[self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(sprintf(__METHOD__ . ' location_code not exist ,$arrLocationIds[%s]', json_encode($arrLocationCodes)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_ADJUST_LOCATION_CODE_NOT_EXIST);
        }
        Bd_Log::trace('huskar call ' . self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION . ' output params ' . json_encode($ret));
        return $ret['result'];
    }

    /**
     * 获取sku库存信息，仓库、库位、效期、良品维度
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array|mixed
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockPeriodStock($intWarehouseId, $arrSkuIds)
    {
        $ret = [];
        if (empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' get sku period stock failed. call ral param is empty.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_RALER_STOCK_PERIOD_DETAIL]['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'sku_ids'      => $strSkuIds,
        ];

        Bd_Log::trace('huskar call ' . self::API_RALER_STOCK_PERIOD_DETAIL . ' input params ' . json_encode($req));
        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $ret = $this->objApiHuskar->getData($req);
        $ret = empty($ret[self::API_RALER_STOCK_PERIOD_DETAIL]) ? [] : $ret[self::API_RALER_STOCK_PERIOD_DETAIL];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' get sku period stock failed. ret is .' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
        }

        Bd_Log::trace('huskar call ' . self::API_RALER_STOCK_PERIOD_DETAIL . ' output params ' . json_encode($ret));
        return $ret['result'];
    }

    /**
     * 库存调整-出库
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $intAdjustType
     * @param $arrDetails
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function adjustStockout($intStockoutOrderId, $intWarehouseId, $intAdjustType, $arrDetails)
    {
        if (empty($intStockoutOrderId) || empty($intWarehouseId) || empty($intAdjustType) || empty($arrDetails)) {
            Bd_Log::warning(__METHOD__ . ' stock adjust decrease order call ral param invalid');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }
        $req[self::API_RALER_ADJUST_STOCKOUT]['requestParams'] = [
            'stockout_order_id' => $intStockoutOrderId,
            'warehouse_id'      => $intWarehouseId,
            'inventory_type'    => $intAdjustType,
            'stockout_details'  => $arrDetails,
        ];
        Bd_Log::trace('huskar call ' . self::API_RALER_ADJUST_STOCKOUT . ' input params ' . json_encode($req));
        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $ret = $this->objApiHuskar->getData($req);
        $ret = empty($ret[self::API_RALER_ADJUST_STOCKOUT]) ? [] : $ret[self::API_RALER_ADJUST_STOCKOUT];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' huskar call stock decrease failed' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        return $ret['result'];
    }
    
    /*
    * 查询商品库存信息
    * @param $intWarehouseId
    * @param $arrSkuIds
    * @return array
    */
    public function getStockInfo($intWarehouseId, $arrSkuIds)
    {
        $ret = [];
        if(empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed. call huskar param is empty.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_RALER_STOCK_DETAIL]['requestParams'] = [
            'warehouse_id' => $intWarehouseId,
            'sku_ids'      => $strSkuIds,
        ];

        Bd_Log::debug('huskar get stock sku info request params: ' . json_encode($req));
        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $ret = $this->objApiHuskar->getData($req);
        Bd_Log::debug('huskar get stock sku info response params: ' . json_encode($ret));
        $ret = empty($ret[self::API_RALER_STOCK_DETAIL]) ? [] : $ret[self::API_RALER_STOCK_DETAIL];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed. call huskar param is empty.' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
        }

        return $ret['result'];
    }

    /***************************************************冻结单相关******************************************************/

    /**
     * 调用库存冻结
     * @param $arrFrozenArg
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function frozenStock($arrFrozenArg)
    {
        $arrReq[self::API_RALER_FROZEN_STOCK]['requestParams'] = $arrFrozenArg;
        Bd_Log::trace('call stock model frozen, req:' . json_encode($arrReq));

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_FROZEN_STOCK]) ? [] : $arrRet[self::API_RALER_FROZEN_STOCK];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
            Bd_Log::warning('call stock model frozen failed. ret: ' . json_encode($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_FROZEN_SKU_STOCK_FAIL);
        }
        Bd_Log::trace('call stock model frozen, ret:' . json_encode($arrRet));

        return $arrRet['result'];
    }

    /**
     * 调用库存解冻
     * @param $arrUnfrozenArg
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function unfrozenStock($arrUnfrozenArg)
    {
        $arrReq[self::API_RALER_UNFROZEN_STOCK]['requestParams'] = $arrUnfrozenArg;
        Bd_Log::trace('call stock model unfrozen, req:' . json_encode($arrReq));

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_UNFROZEN_STOCK]) ? [] : $arrRet[self::API_RALER_UNFROZEN_STOCK];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
            Bd_Log::warning('call stock model unfrozen failed. ret:' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_UNFROZEN_SKU_STOCK_FAIL);
        }
        Bd_Log::trace('call stock model unfrozen, ret:' . json_encode($arrRet));

        return $arrRet['result'];
    }

    /**
     * 获取仓库
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockWarehouse()
    {
        $arrReq[self::API_RALER_GET_STOCK_WAREHOUSE]['requestParams'] = [];

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_GET_STOCK_WAREHOUSE]) ? [] : $arrRet[self::API_RALER_GET_STOCK_WAREHOUSE];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
            Bd_Log::warning('call stock model get stock warehouse failed. ret: ' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_GET_STOCK_WAREHOUSE_FAIL);
        }
        Bd_Log::trace('call stock model get stock warehouse, ret: ' . json_encode($arrRet));

        return $arrRet['result'];
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
     * @param int $intType
     * @return array
     * @throws Nscm_Exception_Error
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
        //仓库ID
        if(empty($intWarehouseId)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is empty');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR);
        }
        $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['warehouse_id'] = $intWarehouseId;

        //商品ID
        if (!empty($intSkuId)) {
            $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['sku_ids'] = $intSkuId;
        }

        //效期类型与时间
        if (!empty($intSkuEffectType) && empty($intTime) || empty($intSkuEffectType) && !empty($intTime)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is error');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR);
        } else if (!empty($intSkuEffectType) && !empty($intTime)) {
            if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $intTime = Order_Util_Stock::formatExpireTime($intTime);
                $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['expiration_time'] = $intTime;
            } else {
                $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['production_time'] = $intTime;
            }
        }

        //质量状态
        if (!empty($intIsDefective)) {
            $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['is_defective'] = $intIsDefective;
        }

        //冻结类型
        $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['type'] = $intType;

        //分页
        if (!empty($intPageNum) && !empty($intPageSize)) {
            $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['page_num'] = $intPageNum;
            $arrReq[self::API_RALER_STOCK_FROZEN_INFO]['requestParams']['page_size'] = $intPageSize;
        }
        Bd_log::trace(sprintf('get stock freezable info, param:%s', json_encode($arrReq)));

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_STOCK_FROZEN_INFO]) ? [] : $arrRet[self::API_RALER_STOCK_FROZEN_INFO];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
            Bd_Log::warning('call stock model get stock freezable info failed. ret: ' . print_r($arrRet, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL);
        }
        Bd_Log::trace('call stock model get stock freezable info, ret: ' . json_encode($arrRet));

        return $arrRet['result'];
    }

    /***************************************************冻结单相关******************************************************/


    /**
     *
     */
    CONST API_RALER_MOVE_LOCATION = 'movelocation';

    /**
     * 获取仓库
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function moveLocation($shift_order_id)
    {
        $arrReq[self::API_RALER_MOVE_LOCATION]['requestParams'] = $shift_order_id;

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_MOVE_LOCATION]) ? [] : $arrRet[self::API_RALER_MOVE_LOCATION];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
            if(310000 == $arrRet['error_no']){
                Bd_Log::warning('call stock method movelocation,idempotency need, ret: ' . json_encode($arrRet));
                return $arrRet['result'];
            }
            throw new Nscm_Exception_Business($arrRet['error_no'],$arrRet['error_msg'] );
        }
        Bd_Log::trace('call stock method movelocation, ret: ' . json_encode($arrRet));

        return $arrRet['result'];
    }

    /**
     *
     */
    CONST API_RALER_GET_REMOVABLE_STOCK = 'getRemovableSkuBatchInfo';

    /**
     * 获取仓库
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getRemovableSkuBatchInfo($arrInput)
    {
        $arrReq[self::API_RALER_GET_REMOVABLE_STOCK]['requestParams'] =
            [
                'warehouse_id'      => $arrInput['warehouse_id'],
                'location_code'     => $arrInput['location_code'],
                'page_num'          => $arrInput['page_num'],
                'page_size'         => $arrInput['page_size'],
            ];

        //商品ID
        if (!empty($arrInput['sku_id'])) {
            $arrReq[self::API_RALER_GET_REMOVABLE_STOCK]['requestParams']['sku_ids'] = $arrInput['sku_id'];
        }

        //质量状态
        if (!empty($arrInput['is_defective'])) {
            $arrReq[self::API_RALER_GET_REMOVABLE_STOCK]['requestParams']['is_defective'] = $arrInput['is_defective'];
        }

        $intSkuEffectType = $arrInput['sku_effect_type'];
        $intTime = $arrInput['production_or_expiration_time'];
        //效期类型与时间
        if (!empty($intSkuEffectType) && empty($intTime) || empty($intSkuEffectType) && !empty($intTime)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is error');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR);
        } else if (!empty($intSkuEffectType) && !empty($intTime)) {
            if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $intTime = Order_Util_Stock::formatExpireTime($intTime);
                $arrReq[self::API_RALER_GET_REMOVABLE_STOCK]['requestParams']['expiration_time'] = $intTime;
            } else {
                $arrReq[self::API_RALER_GET_REMOVABLE_STOCK]['requestParams']['production_time'] = $intTime;
            }
        }

        $this->objApiHuskar->setFormat(new Order_Util_HuskarFormat());
        $arrRet = $this->objApiHuskar->getData($arrReq);
        $arrRet = empty($arrRet[self::API_RALER_GET_REMOVABLE_STOCK]) ? [] : $arrRet[self::API_RALER_GET_REMOVABLE_STOCK];
        if (empty($arrRet) || !empty($arrRet['error_no'])) {
//            Bd_Log::warning('call stock model get location stock failed. ret: ' . print_r($arrRet, true));
            throw new Nscm_Exception_Business($arrRet['error_no'],$arrRet['error_msg'] );
//            Order_BusinessError::throwException(Order_Error_Code::SHIFT_ORDER_GET_LOCATION_STOCK_FAILED);
        }
        Bd_Log::trace('call stock model get location stock, ret: ' . json_encode($arrRet));

        return $arrRet['result'];
    }

    /***************************************************移位单相关******************************************************/

    /**
     * 冻结库存
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $arrFreezeStockDetail
     * @return mixed
     * @throws Order_BusinessError
     * @throws Nscm_Exception_Error
     */
    public function freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail)
    {
        $objApiRaler = new Nscm_Lib_ApiHuskar();
        $objApiRaler->setFormat(new Order_Util_HuskarFormat());
        $arrRequestParams['stockout_order_id'] = $intStockoutOrderId;
        $arrRequestParams['warehouse_id'] = $intWarehouseId;
        $arrRequestParams['freeze_details'] = $arrFreezeStockDetail;
        $arrReq[self::API_RALER_RESERVE_STOCK]['requestParams'] = $arrRequestParams;

        $arrRet = $objApiRaler->getData($arrReq);
        Bd_Log::trace(sprintf("method[%s] params[%s] ret[%s]",
            __METHOD__, json_encode($arrReq), json_encode($arrRet)));
        if (false === $arrRet || !empty($arrRet['errno'])) {
            Bd_Log::warning(sprintf("reserve sku stock failed params[%s] ret[%s]",
                json_encode($arrReq), json_encode($arrRet)));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL);
        }
        return $arrRet[self::API_RALER_RESERVE_STOCK];
    }
}
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
     * freeze sku stock
     * @var string
     */
    const API_RALER_FREEZE_SKU_STOCK = 'freezeskustock';

    /**
     * freeze sku stock
     * @var string
     */
    const API_RALER_UNFREEZE_SKU_STOCK = 'unfreezeskustock';

    /**
     * 库存调整
     * @var string
     */
    const  API_RALER_ADJUST_SKU_STOCK = 'adjustskustock';


    /**
     * 冻结单
     * @var string
     */
    const  API_RALER_FROZEN_STOCK = 'frozenorderskustock';

    /**
     * 冻结单——解冻
     * @var string
     */
    const  API_RALER_UNFROZEN_STOCK = 'unfrozenorderskustock';

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
     * 获取仓库商品冻结数据
     * @var string
     */
    const  API_RALER_STOCK_FROZEN_INFO = 'getskubatchfreezableinfo';

    /**
     * 库存调整-出库
     * @var string
     */
    const  API_RALER_STOCK_IN = 'stockin';

    /**
     * 拣货--获取商品库区库位
     * @var string
     */
    //TODO 修改API&添加配置
    const  API_RALER_GET_SKU_LOCATION = 'getskulocation';

    /**
     * 库位--批量获取库位信息
     * @var string
     */
    const  API_HUSKAR_GET_BATCH_STORAGE_LOCATION = 'getbatchstoragelocation';

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
     * @param $locationCodes
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getBatchStorageLocation($intWarehouseId, $arrLocationCodes)
    {
        $ret = [];
        if (empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' get sku period stock failed. call ral param is empty.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION]['requestParams'] = [
            'warehouse_id'   => $intWarehouseId,
            'location_codes' => $strSkuIds,
        ];

        Bd_Log::trace('huskar call ' . self::API_HUSKAR_GET_BATCH_STORAGE_LOCATION . ' input params ' . json_encode($req));
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
     * @param int $intStockoutOrderId
     * @param int $intWarehouseId
     * @param int $intAdjustType
     * @param array $arrDetails
     * @return array
     */
    public function adjustStockout($intStockoutOrderId, $intWarehouseId, $intAdjustType, $arrDetails)
    {
        $ret = [];

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

        $ret = $this->objApiHuskar->getData($req);
        $ret = empty($ret[self::API_RALER_ADJUST_STOCKOUT]) ? [] : $ret[self::API_RALER_ADJUST_STOCKOUT];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' huskar call stock decrease failed' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        return $ret;
    }

}
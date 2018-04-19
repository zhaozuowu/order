<?php
/**
 * @name Dao_Ral_Stock
 * @desc stock ral dao
 * @author jinyu02(jinyu02@iwaimai.baidu.com)
 */

class Dao_Ral_Stock
{

    /**
     * api raler 
     * @var Order_ApiRaler
     */
    protected $objApiRal;

    /**
     * init
     */
    public function __construct()
    {
        $this->objApiRal = new Order_ApiRaler();
    }

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
     * 作废出库单
     */
    const  API_RALER_CANCEL_FREEZESKU_STOCK = 'cancelfreezeskustock';


    /**
     * 获取仓库某商品当前库存详情
     * @var string
     */
    const  API_RALER_STOCK_DETAIL = 'stockdetail';

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
     * freeze sku stock
     * @param integer $intStockoutOrderId
     * @param integer $intWarehouseId
     * @param $arrFreezeDetail
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeDetail) {
        $ret = [];
        if (empty($intStockoutOrderId) ||empty($intWarehouseId) || empty($arrFreezeDetail)) {
            return $ret;
        }
        if (!empty($intStockoutOrderId)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['stockout_order_id'] = $intStockoutOrderId;
        }
        if (!empty($intWarehouseId)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['warehouse_id'] = $intWarehouseId;
        }
        if (!empty($arrFreezeDetail)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['freeze_details'] = $arrFreezeDetail;
        }
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_FREEZE_SKU_STOCK]) ? [] : $ret[self::API_RALER_FREEZE_SKU_STOCK];
        Bd_Log::trace(sprintf("method[%s] ret[%s]", __METHOD__, json_encode($ret)));
        return $ret;
    }

    /**
     * unfreeze sku stock
     * @param array $intStockoutOrderId
     * @param array $intWarehouseId
     * @param array $arrStockoutDetail
     * @return array
     * @throws Order_BusinessError
     * @throws Nscm_Exception_Error
     */
    public function unfreezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrStockoutDetail) {
        $ret = [];
        if (empty($intStockoutOrderId) || empty($intWarehouseId) || empty($arrStockoutDetail)) {
            return $ret;
        }         
        if (!empty($intStockoutOrderId)) {
            $req[self::API_RALER_UNFREEZE_SKU_STOCK]['stockout_order_id'] = $intStockoutOrderId;
        }
        if (!empty($intWarehouseId)) {
            $req[self::API_RALER_UNFREEZE_SKU_STOCK]['warehouse_id'] = $intWarehouseId;
        }
        if (!empty($arrStockoutDetail)) {
            $req[self::API_RALER_UNFREEZE_SKU_STOCK]['stockout_details'] = $arrStockoutDetail;
        }
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug("unfreezeSkuStock res:".json_encode($ret).",request data:".json_encode($req));
        $ret = empty($ret[self::API_RALER_UNFREEZE_SKU_STOCK]) ? [] : $ret[self::API_RALER_UNFREEZE_SKU_STOCK];
        if (empty($ret) || !empty($ret['error_no'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL);
        }
        return true;
    }

    /**
     * 查询商品库存信息
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array
     */
    public function getStockInfo($intWarehouseId, $arrSkuIds)
    {
        $ret = [];
        if(empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed. call ral param is empty.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_RALER_STOCK_DETAIL]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_STOCK_DETAIL]['sku_ids'] = $strSkuIds;


        Bd_Log::debug('ral get stock sku info request params: ' . json_encode($req));
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug('ral get stock sku info response params: ' . json_encode($ret));
        $ret = empty($ret[self::API_RALER_STOCK_DETAIL]) ? [] : $ret[self::API_RALER_STOCK_DETAIL];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed. call ral param is empty.' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
        }

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

        if(empty($intStockoutOrderId) || empty($intWarehouseId) || empty($intAdjustType) || empty($arrDetails)) {
            Bd_Log::warning(__METHOD__ . ' stock adjust decrease order call ral param invalid');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        foreach ($arrDetails as $detail) {
            if(empty($detail['sku_id']) || empty($detail['stockout_amount'])) {
                Bd_Log::warning(__METHOD__ . ' stock adjust decrease order call ral param invalid');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
            }
        }

        $req[self::API_RALER_ADJUST_STOCKOUT]['stockout_order_id'] = $intStockoutOrderId;
        $req[self::API_RALER_ADJUST_STOCKOUT]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_ADJUST_STOCKOUT]['inventory_type'] = $intAdjustType;
        $req[self::API_RALER_ADJUST_STOCKOUT]['stockout_details'] = $arrDetails;

        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_ADJUST_STOCKOUT]) ? [] : $ret[self::API_RALER_ADJUST_STOCKOUT];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' ral call stock decrease failed' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        Bd_Log::trace(__METHOD__ . ' ral call stock decrease success ' . json_encode($ret));
        return $ret;
    }


    /**
     * 库存调整（扣减）
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $arrStockoutDetail
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function adjustSkuStock($intStockoutOrderId, $intWarehouseId, $arrStockoutDetail)
    {
        $ret = [];
        if (empty($intStockoutOrderId) || empty($intWarehouseId) || empty($arrStockoutDetail)) {
            return $ret;
        }
        if (!empty($intStockoutOrderId)) {
            $req[self::API_RALER_ADJUST_SKU_STOCK]['stockout_order_id'] = $intStockoutOrderId;
        }
        if (!empty($intWarehouseId)) {
            $req[self::API_RALER_ADJUST_SKU_STOCK]['warehouse_id'] = $intWarehouseId;
        }
        if (!empty($arrSkuDetail)) {
            $req[self::API_RALER_ADJUST_SKU_STOCK]['stockout_detail'] = $arrStockoutDetail;
        }
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_ADJUST_SKU_STOCK]) ? [] : $ret[self::API_RALER_ADJUST_SKU_STOCK];
        if (empty($ret) || !empty($ret['error_no'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ADJUST_SKU_STOCK_FAIL);
        }
        return $ret;

    }

    //-----------------------------------------冻结单-------------------------------------------------------

    /**
     * @param $arrFrozenArg
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function frozenStock($arrFrozenArg)
    {
        $req[self::API_RALER_FROZEN_STOCK] = $arrFrozenArg;

        Nscm_Lib_Singleton::get('Nscm_Lib_ApiRaler')->setFormat(new Order_Util_Format());
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_FROZEN_STOCK]) ? [] : $ret[self::API_RALER_FROZEN_STOCK];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning('ral call stock model frozen sku failed. ret: ' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_FROZEN_SKU_STOCK_FAIL);
        }

        return $ret;
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
        $req[self::API_RALER_UNFROZEN_STOCK] = $arrUnfrozenArg;

        Nscm_Lib_Singleton::get('Nscm_Lib_ApiRaler')->setFormat(new Order_Util_Format());
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_FROZEN_STOCK]) ? [] : $ret[self::API_RALER_FROZEN_STOCK];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning('ral call stock model unfrozen sku failed. ret: ' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_UNFROZEN_SKU_STOCK_FAIL);
        }

        return $ret;
    }
    //-----------------------------------------冻结单-------------------------------------------------------



    /**
     * @param $intStockoutOrderId
     * @param $warehouse_id
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function cancelfreezeskustock($intStockoutOrderId, $intWarehouseId)
    {
        $ret = [];
        if (empty($intStockoutOrderId) || empty($intWarehouseId)) {
            return $ret;
        }
        if (!empty($intStockoutOrderId)) {
            $req[self::API_RALER_CANCEL_FREEZESKU_STOCK]['stockout_order_id'] = $intStockoutOrderId;
        }
        if (!empty($intWarehouseId)) {
            $req[self::API_RALER_CANCEL_FREEZESKU_STOCK]['warehouse_id'] = $intWarehouseId;
        }
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug("cancelfreezeskustock res:".json_encode($ret).",request data:".json_encode($req));
        $ret = empty($ret[self::API_RALER_CANCEL_FREEZESKU_STOCK]) ? [] : $ret[self::API_RALER_CANCEL_FREEZESKU_STOCK];
        if (empty($ret) || !empty($ret['error_no'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
        }
        return $ret;
    }

    /**
     * 获取仓库商品可冻结数据
     * @param $intWarehouseId
     * @param $intSkuId
     * @param $intIsDefective
     * @return array|mixed
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockFrozenInfo($intWarehouseId, $intSkuId, $intIsDefective)
    {
        if(empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock frozen info failed, call ral param is empty');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL);
        }

        $req[self::API_RALER_STOCK_FROZEN_INFO]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_STOCK_FROZEN_INFO]['sku_ids'] = $intSkuId;
        if (!empty($intIsDefective)) {
            $req[self::API_RALER_STOCK_FROZEN_INFO]['is_defective'] = $intIsDefective;
        }

        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_STOCK_FROZEN_INFO]) ? [] : $ret[self::API_RALER_STOCK_FROZEN_INFO];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed, result is empty.' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL);
        }
        Bd_log::debug(sprintf('get stock frozen info, param:%s, ret:%s', json_encode($req), json_encode($ret)));

        return $ret;
    }

    /**
     * 入库单入库库存通知质量状态
     * @param int   $intStockInOrderId 入库单号
     * @param int   $intStockInOrderType 入库单类型 1--采购入库 2--销退入库 3--盘盈入库
     * @param int   $intWarehouseId 入库所属仓库id
     * @param array $arrStockInSkuList 入库sku-list
     * @param int   $intVendorId 供货商id 采购入库时
     * @return array|mixed
     * @throws Nscm_Exception_Business
     * @throws Nscm_Exception_System
     */
    public function stockIn($intStockInOrderId, $intStockInOrderType, $intWarehouseId, $arrStockInSkuList, $intVendorId = 0)
    {
        $ret = [];
        if (empty($intStockInOrderId) || empty($intStockInOrderType) || empty($intWarehouseId) || empty($arrStockInSkuList)) {
            return $ret;
        }
        $req = [
            'stockin_order_id' => $intStockInOrderId,
            'stockin_order_type' => $intStockInOrderType,
            'warehouse_id' => $intWarehouseId,
            'vendor_id' => $intVendorId,
            'stockin_sku_info' => $arrStockInSkuList,
        ];
        $ret = Nscm_Service_Stock::stockin($req);
        Bd_Log::debug("stockin res:".json_encode($ret).",request data:".json_encode($req));
        return $ret;
    }

}
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
        Bd_Log::trace(__METHOD__ . ' 库存调整-查看库存 参数 intWarehouseId=' . $intWarehouseId);
        Bd_Log::trace(__METHOD__ . ' 库存调整-查看库存 参数 arrSkuIds=' . json_encode($arrSkuIds));

        $ret = [];
        if(empty($intWarehouseId) || empty($arrSkuIds)) {
            Bd_Log::warning(__METHOD__ . ' 库存调整-出库 ral 调用失败. 参数为空');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_RALER_STOCK_DETAIL]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_STOCK_DETAIL]['sku_ids'] = $strSkuIds;


        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_STOCK_DETAIL]) ? [] : $ret[self::API_RALER_STOCK_DETAIL];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' 库存调整-出库 ral 调用失败' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
        }

        Bd_Log::trace(__METHOD__ . ' 库存调整-查看库存 ral 调用成功' . json_encode($ret));
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
        Bd_Log::trace(__METHOD__ . ' 库存调整-出库 参数 intStockoutOrderId=' . $intStockoutOrderId);
        Bd_Log::trace(__METHOD__ . ' 库存调整-出库 参数 intWarehouseId=' . $intWarehouseId);
        Bd_Log::trace(__METHOD__ . ' 库存调整-出库 参数 intAdjustType=' . $intAdjustType);
        Bd_Log::trace(__METHOD__ . ' 库存调整-出库 参数 arrDetails=' . json_encode($arrDetails));


        if(empty($intStockoutOrderId) || empty($intWarehouseId) || empty($intAdjustType) || empty($arrDetails)) {
            Bd_Log::warning(__METHOD__ . ' 库存调整-出库 ral 调用失败. 参数为空');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
            return $ret;
        }

        foreach ($arrDetails as $detail) {
            if(empty($detail['sku_id']) || empty($detail['stockout_amount'])) {
                Bd_Log::warning(__METHOD__ . ' 库存调整-出库 ral 调用失败. detail中参数为空');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
                return $ret;
            }
        }

        $req[self::API_RALER_ADJUST_STOCKOUT]['stockout_order_id'] = $intStockoutOrderId;
        $req[self::API_RALER_ADJUST_STOCKOUT]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_ADJUST_STOCKOUT]['inventory_type'] = $intAdjustType;
        $req[self::API_RALER_ADJUST_STOCKOUT]['stockout_details'] = $arrDetails;

        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_ADJUST_STOCKOUT]) ? [] : $ret[self::API_RALER_ADJUST_STOCKOUT];
        if (empty($ret) || !empty($ret['error_no'])) {
            Bd_Log::warning(__METHOD__ . ' 库存调整-出库 ral 调用失败' . print_r($ret, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        Bd_Log::trace(__METHOD__ . ' 库存调整-出库 ral 调用成功 ' . json_encode($ret));
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

}
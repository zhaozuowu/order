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
     * @param integer $arrSkuDetail
     * @return array
     */
    public function freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrSkuDetail) {
        $ret = [];
        if (empty($intStockoutOrderId) ||empty($intWarehouseId) || empty($arrSkuDetail)) {
            return $ret;
        }
        if (!empty($intStockoutOrderId)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['stockout_order_id'] = $intStockoutOrderId;
        }
        if (!empty($intWarehouseId)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['warehouse_id'] = $intWarehouseId;
        }
        if (!empty($arrSkuDetail)) {
            $req[self::API_RALER_FREEZE_SKU_STOCK]['freeze_details'] = $arrSkuDetail;
        }
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_FREEZE_SKU_STOCK]) ? [] : $ret[self::API_RALER_FREEZE_SKU_STOCK];
        
        if (empty($ret) || !empty($ret['error_no'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL);
        }
        return $ret;
    }

    /**
     * unfreeze sku stock
     * @param array $intStockoutOrderId
     * @param array $intWarehouseId
     * @param array $arrStockoutDetail
     * @return void
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
            return $ret;
        }

        $strSkuIds = implode(',', $arrSkuIds);

        $req[self::API_RALER_STOCK_DETAIL]['warehouse_id'] = $intWarehouseId;
        $req[self::API_RALER_STOCK_DETAIL]['sku_ids'] = $strSkuIds;


        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_STOCK_DETAIL]) ? [] : $ret[self::API_RALER_STOCK_DETAIL];
        if (empty($ret) || !empty($ret['error_no'])) {
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
            Bd_Log::warning(__METHOD__ . ' 参数为空');
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            return $ret;
        }

        foreach ($arrDetails as $detail) {
            if(empty($detail['sku_id']) || empty($detail['stockout_amount'])) {
                Bd_Log::warning(__METHOD__ . ' 参数为空');
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
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
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL);
        }

        Bd_Log::debug(__METHOD__ . ' 库存调整-出库 ral 调用成功');
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
    
}
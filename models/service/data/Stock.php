<?php
/**
 * @name Service_Data_Stock
 * @desc stock service data
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Data_Stock 
{   
    /**
     * @var Dao_Ral_Stock
     */
    protected $objDaoStock;

    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoStock = new Dao_Ral_Stock();
        $this->objDaoSku = new Dao_Ral_Sku();
    }

    /**
     * 冻结库存
     * @param $intSkuId
     * @param $intWarehouseId
     * @param $arrStockDetail
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function freezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail) {
        return $this->objDaoStock->freezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail);
    }

    /**
     * 解冻库存
     * @param $intSkuId
     * @param $intWarehouseId
     * @param $arrStockDetail
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function unfreezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail) {
        return $this->objDaoStock->unfreezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail);
    }

    /**
     * 获取仓库某商品当前库存详情
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getStockInfo($intWarehouseId, $arrSkuIds)
    {
        $arrRet = [];
        $arrStockInfo = $this->objDaoStock->getStockInfo($intWarehouseId, $arrSkuIds);
        if(empty($arrStockInfo)) {
            return $arrRet;
        }

        $arrSkuInfo = $this->objDaoSku->getSkuInfos($arrSkuIds);

        foreach ($arrStockInfo as $value) {
            $intSkuId = $value['sku_id'];
            if(!empty($arrSkuInfo[$intSkuId])) {
                $arrRet[] = array_merge($arrSkuInfo[$intSkuId], $value);
            } else {
                $arrRet[] = $value;
            }
        }

        return $arrRet;
    }

    /**
     * 获取仓库商品可冻结批次数据
     * @param $intWarehouseId
     * @param $intSkuId
     * @param $intIsDefective
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockFrozenInfo($intWarehouseId, $intSkuId, $intIsDefective)
    {
        $arrRet = [];
        $arrStockFrozenInfo = $this->objDaoStock->getStockFrozenInfo(
            $intWarehouseId,
            $intSkuId,
            $intIsDefective
        );
        if (empty($arrStockFrozenInfo)) {
            return $arrRet;
        }

        $arrSkuInfo = $this->objDaoSku->getSkuInfos($intSkuId);
        foreach ($arrStockFrozenInfo as $arrItem) {
            $intSkuId = $arrItem['sku_id'];
            if(!empty($arrSkuInfo[$intSkuId])) {
                $arrRet[] = array_merge($arrSkuInfo[$intSkuId], $arrItem);
            } else {
                $arrRet[] = $arrItem;
            }
        }

        return $arrRet;
    }

    /**
     * 解冻
     * @param $arrInput
     * @param $arrSkuInfos
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function frozenSkuStock($arrInput, $arrSkuInfos)
    {
        $arrPram = [
            'warehouse_id' => $arrInput['warehouse_id'],
            'ext_order_id' => $arrInput['stock_frozen_order_id'],
            'frozen_type'  => $arrInput['frozen_type'],
        ];
        foreach ($arrInput['detail'] as $arrItem) {
            $arrSkuInfo = $arrSkuInfos[$arrItem['sku_id']];
            $intExpireTime = Order_Util_Stock::getExpireTime(
                $arrItem['production_or_expire_time'],
                $arrSkuInfo['sku_effect_type'],
                $arrSkuInfo['sku_effect_day']
            );
            $arrDetail = [
                'sku_id' => $arrItem['sku_id'],
                'frozen_amount' => $arrItem['current_frozen_amount'],
                'unfreeze_amount' => $arrItem['unfrozen_amount'],
                'is_defective' => $arrItem['is_defective'],
                'expiration_time' => $intExpireTime
            ];
            $arrPram['details'][] = $arrDetail;
        }

        $this->objDaoStock->unfrozenStock($arrPram);
    }

}

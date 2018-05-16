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
     * @var Dao_Huskar_Stock
     */
    protected $objDaoHuskarStock;

    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoSku = new Dao_Ral_Sku();
        $this->objDaoStock = new Dao_Ral_Stock();
        $this->objDaoHuskarStock = new Dao_Huskar_Stock();
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
     * 获取sku库存，仓库、效期、良品 维度
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockPeriodStock($intWarehouseId, $arrSkuIds) {
        $arrRet = [];

        $arrStockInfo = $this->objDaoHuskarStock->getStockPeriodStock($intWarehouseId, $arrSkuIds);
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
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getStockFrozenInfo($arrInput)
    {
        $arrRet = [];

        //get stock info
        $arrStockFrozenInfo = $this->objDaoHuskarStock->getStockFrozenInfo(
            $arrInput['warehouse_id'],
            $arrInput['sku_id'],
            $arrInput['is_defective'],
            $arrInput['sku_effect_type'],
            $arrInput['production_or_expiration_time'],
            $arrInput['page_num'],
            $arrInput['page_size']
        );
        if (empty($arrStockFrozenInfo['total']) || empty($arrStockFrozenInfo['detail'])) {
            return $arrRet;
        }

        //get sku info
        $arrSkuInfo = $this->objDaoSku->getSkuInfos(array_unique(array_column($arrStockFrozenInfo['detail'], 'sku_id')));

        //merge detail
        $arrSkus = [];
        foreach ($arrStockFrozenInfo['detail'] as $arrItem) {
            $intSkuId = $arrItem['sku_id'];
            if(!empty($arrSkuInfo[$intSkuId])) {
                $arrSkus[] = array_merge($arrSkuInfo[$intSkuId], $arrItem);
            } else {
                $arrSkus[] = $arrItem;
            }
        }

        //return
        $arrRet['total'] = $arrStockFrozenInfo['total'];
        $arrRet['skus'] = $arrSkus;
        return $arrRet;
    }

}

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
     * @param integer $intSkuId
     * @param integer $intWarehouseId
     * @param integer $arrStockDetail
     * @return array
     */
    public function freezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail) {
        return $this->objDaoStock->freezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail);
    }

    /**
     * 解冻库存
     * @param integer $intSkuId
     * @param integer $intWarehouseId
     * @param integer $arrStockDetail
     * @return array
     */
    public function unfreezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail) {
        return $this->objDaoStock->unfreezeSkuStock($intSkuId, $intWarehouseId, $arrStockDetail);
    }

    /**
     * 获取仓库某商品当前库存详情
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array
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
            }
        }

        return $arrRet;
    }

    /**
     * 库存调整-出库
     * @param $intStockoutOrderId
     * @param $intWarehouseId
     * @param $intAdjustType
     * @param $arrDetails
     * @return array
     */
    public function adjustStockout($intStockoutOrderId, $intWarehouseId, $intAdjustType, $arrDetails)
    {
        return $this->objDaoStock->adjustStockout($intStockoutOrderId, $intWarehouseId, $intAdjustType, $arrDetails);
    }
}

<?php
/**
 * @name Dao_Ral_Sku
 * @desc sku ral dao
 * @author wanggang(wanggang01@iwaimai.baidu.com)
 */

class Dao_Ral_Sku
{
    /**
     * api raler
     * @var Order_ApiRaler
     */
    protected $objApiRal;

    /**
     * get sku list
     * @var string
     */
    const API_RALER_GET_SKU_LIST = 'getskulist';

    /**
     * init
     */
    public function __construct()
    {
        $this->objApiRal = new Order_ApiRaler();
    }

    /**
     * get sku list
     * @param  string $strPageSize
     * @param  string $strSkuId
     * @param  string $strUpcId
     * @param  string $strSkuName
     * @param  string $strSkuCategory1
     * @param  string $strSkuCategory2
     * @param  string $strSkuCategory3
     * @param  string $strPageNum
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getSkuList($strPageSize, $strSkuId = '', $strUpcId = '', $strSkuName = '',
        $strSkuCategory1 = '', $strSkuCategory2 = '', $strSkuCategory3 = '', $strPageNum = '1')
    {
        $ret = [];
        if (empty($strPageSize)) {
            return $ret;
        }
        $req[self::API_RALER_GET_SKU_LIST] = [
            'page_num' => $strPageNum,
            'page_size' => empty($strPageSize)?'20':$strPageSize,
            'sku_id' => empty($strSkuId)?'':$strSkuId,
            'upc_id' => empty($strUpcId)?'':$strUpcId,
            'sku_name' => empty($strSkuName)?'':$strSkuName,
            'sku_category_1' => empty($strSkuCategory1)?'':$strSkuCategory1,
            'sku_category_2' => empty($strSkuCategory2)?'':$strSkuCategory2,
            'sku_category_3' => empty($strSkuCategory3)?'':$strSkuCategory3,
        ];
        $ret = $this->objApiRal->getData($req);
        $ret = !empty($ret[self::API_RALER_GET_SKU_LIST])?$ret[self::API_RALER_VENDOR_SUG]:[];
        return $ret;
    }
}

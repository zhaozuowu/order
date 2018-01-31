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
     * get sku info
     * @var string
     */
    const API_RALER_GET_SKU_INFO = 'getskuinfo';

    /**
     * get sku infos
     * @var string
     */
    const API_RALER_GET_SKU_INFOS = 'getskuinfos';

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
            'page_num' => empty($strPageNum)?'0':$strPageNum,
        ];
        if (!empty($strPageSize)) {
            $req[self::API_RALER_GET_SKU_LIST]['page_size'] = $strPageSize;
        }
        if (!empty($strSkuId)) {
            $req[self::API_RALER_GET_SKU_LIST]['sku_id'] = $strSkuId;
        }
        if (!empty($strUpcId)) {
            $req[self::API_RALER_GET_SKU_LIST]['upc_id'] = $strUpcId;
        }
        if (!empty($strSkuName)) {
            $req[self::API_RALER_GET_SKU_LIST]['sku_name'] = $strSkuName;
        }
        if (!empty($strSkuCategory1)) {
            $req[self::API_RALER_GET_SKU_LIST]['sku_category_1'] = $strSkuCategory1;
        }
        if (!empty($strSkuCategory2)) {
            $req[self::API_RALER_GET_SKU_LIST]['sku_category_2'] = $strSkuCategory2;
        }
        if (!empty($strSkuCategory3)) {
            $req[self::API_RALER_GET_SKU_LIST]['sku_category_3'] = $strSkuCategory3;
        }
        Bd_Log::debug('ral getSkuList input params: ' . json_encode($req));
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug('ral getSkuList out params: ' . json_encode($ret));
        $ret = !empty($ret[self::API_RALER_GET_SKU_LIST])?$ret[self::API_RALER_GET_SKU_LIST]:[];
        return $ret;
    }

    /**
     * get sku info 
     * @param integer $intSkuId
     * @return array
     * @throws
     */
    public function getSkuInfo($intSkuId)
    {
        $ret = [];
        if (empty($intSkuId)) {
            return $ret;
        }
        if (!empty($intSkuId)) {
            $req[self::API_RALER_GET_SKU_INFO]['sku_id'] = $intSkuId;
        }
        $ret = $this->objApiRal->getData($req);
        $ret = empty($ret[self::API_RALER_GET_SKU_INFO]) ? [] : $ret[self::API_RALER_GET_SKU_INFO];
        return $ret;
    }

    /**
     * get sku infos
     * @param int[] $arrSkuIds
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getSkuInfos($arrSkuIds)
    {
        $intCountInput = count($arrSkuIds);
        $arrSkus = implode(',', $arrSkuIds);
        $req = [
            self::API_RALER_GET_SKU_INFOS => [
                'sku_ids' => $arrSkus,
            ],
        ];
        Bd_Log::debug('ral getSkuInfos input params: ' . json_encode($req));
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug('ral getSkuInfos out params: ' . json_encode($ret));
        $arrSkuInfos = [];
        if (empty($ret[self::API_RALER_GET_SKU_INFOS]['skus'])) {
            return $arrSkuInfos;
        }
        foreach ($ret[self::API_RALER_GET_SKU_INFOS]['skus'] as $row) {
            $arrSkuInfos[$row['sku_id']] = $row;
        }
        $intCountOutput = count($arrSkuInfos);
        if ($intCountInput != $intCountOutput) {
            Bd_Log::warning('io sku count not equal');
        }
        return $arrSkuInfos;
    }
}

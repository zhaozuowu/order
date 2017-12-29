<?php
/**
 * @name Service_Data_Sku
 * @desc sku service data
 * @author wanggang01@iwaimai.baidu.com
 */

class Service_Data_Sku
{
    /**
     * sku dao
     * @var Service_Data_Sku
     */
    protected $objSkuDao;

    /**
     * init
     */
    public function __construct()
    {
        $this->objSkuDao = new Dao_Ral_Sku();
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
     */
    public function getSkuList($strPageSize, $strSkuId = '', $strUpcId = '', $strSkuName = '',
                               $strSkuCategory1 = '', $strSkuCategory2 = '', $strSkuCategory3 = '', $strPageNum = '1')
    {
        $ret = $this->objSkuDao->getSkuList($strPageSize, $strSkuId, $strUpcId, $strSkuName,
            $strSkuCategory1, $strSkuCategory2, $strSkuCategory3, $strPageNum);
        return $ret;
    }
}

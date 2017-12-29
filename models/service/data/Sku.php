<?php

/**
 * @name Service_Data_Sku
 * @desc 获取彩云sku信息
 * @author jinyu02@iwaimai.baidu.com　
 */
class Service_Data_Sku {

    /**
     * @var Dao_Rpc
     */
    protected $objDaoRpc;

    /**
     * init
     */
    public function __contruct() {
        $this->objDaoRpc = new Dao_Rpc();
    }

    /**
     * 获取彩云sku信息访问参数
     * @param int $strSkuId
     * @return array
     */
    protected function getSkuInfoApiParams($intSkuId) {
        $req['getskuinfo'] = [];
        $req['getskuinfo']['sku_id'] = intval($intSkuId);
        return $req;
    }

    /**
     * 通过sku_id获取sku信息
     * @return array
     */
    public function getSkuInfoBySkuId($intSkuId) {
        if (empty($intSkuId)) {
            
        }
        $arrReq = $this->getSkuInfoApiParams($intSkuId);
        if (empty($arrReq)) {

        }
        return $this->objDaoRpc->getData($arrReq);
    } 
}
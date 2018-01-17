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

    /**
     * 通过sku_id获取sku信息
     * @param  integer $intSkuId
     * @return array
     * @throws Order_BusinessError
     */
    public function getSkuInfoBySkuId($intSkuId) {
        if (empty($intSkuId)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SKU_ID_EMPTY);    
        }
        return $this->objSkuDao->getSkuInfo($intSkuId);
    }

    /**
     * 拼接sku详细信息
     * @param array $arrBatchSkuParams
     * @param integer $intOrderType
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function appendSkuInfosToSkuParams($arrBatchSkuParams, $intOrderType) {
        if (empty($arrBatchSkuParams)) {
            return [];
        }
        $arrSkuIds = array_column($arrBatchSkuParams, 'sku_id');
        $arrMapSkuInfos = $this->objSkuDao->getSkuInfos($arrSkuIds);
        if (empty($arrMapSkuInfos)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_SKU_FAILED);
        }
        foreach ((array)$arrBatchSkuParams as $intKey => $arrSkuItem) {
            if (empty($arrSkuItem['sku_id'])) {
                continue;
            }
            $intSkuId = $arrSkuItem['sku_id'];
            $arrBatchSkuParams[$intKey]['sku_name'] = $arrMapSkuInfos[$intSkuId]['sku_name'];
            $arrBatchSkuParams[$intKey]['sku_net'] = $arrMapSkuInfos[$intSkuId]['sku_net'];
            $arrBatchSkuParams[$intKey]['sku_net_unit'] = $arrMapSkuInfos[$intSkuId]['sku_net_unit'];
            $arrBatchSkuParams[$intKey]['upc_id'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_id'];
            $arrBatchSkuParams[$intKey]['upc_unit'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_unit'];
            $arrBatchSkuParams[$intKey]['upc_unit_num'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_unit_num'];
            $arrBatchSkuParams[$intKey]['sku_effect_type'] = $arrMapSkuInfos[$intSkuId]['sku_effect_type'];
            $arrBatchSkuParams[$intKey]['sku_effect_day'] = $arrMapSkuInfos[$intSkuId]['sku_effect_day'];
            $arrBatchSkuParams[$intKey]['sku_business_form'] = implode(',', $arrMapSkuInfos[$intSkuId]['sku_business_form']);
            $arrBatchSkuParams[$intKey]['send_price_info'] =
                $this->getSendPriceInfo($arrMapSkuInfos[$intSkuId]['sku_business_form_detail'], $intOrderType);
            $arrBatchSkuParams[$intKey]['sku_tax_rate'] = $arrMapSkuInfos[$intSkuId]['sku_tax_rate'];
        }
        return $arrBatchSkuParams;
    }

    /**
     * @param $arrBusinessFormDetail
     * @param $intOrderType
     * @return array|mixed
     */
    protected function getSendPriceInfo($arrBusinessFormDetail, $intOrderType) {
        $arrSendPriceInfo = [];
        foreach ((array)$arrBusinessFormDetail as $arrBusinessFormItem) {
            if ($intOrderType != $arrBusinessFormItem['type']) {
                continue;
            }
            $arrSendPriceInfo = $arrBusinessFormItem;
        }
        return $arrSendPriceInfo;
    }
}

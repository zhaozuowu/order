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
     * @param $arrBatchSkuParams
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function appendSkuEventInfosToSkuParams($arrBatchSkuParams)
    {
        if (empty($arrBatchSkuParams)) {
            return [];
        }
        $arrSkuIds = array_column($arrBatchSkuParams, 'sku_id');
        $arrMapSkuInfos = $this->objSkuDao->getSkuInfos($arrSkuIds);
        if (empty($arrMapSkuInfos)) {
            return [];
        }
        $list = [];
        foreach ($arrBatchSkuParams as $intKey=>$arrSkuItem) {
            if (empty($arrMapSkuInfos[$arrSkuItem['sku_id']]) || !array_key_exists($arrSkuItem['event_type'],Order_Define_StockoutOrder::SKUS_EVENTS_IS_BACK_MAP)) {
                continue;
            }
            $tmp['skuId'] = $arrSkuItem['sku_id'];
            $tmp['name'] = $arrMapSkuInfos[$arrSkuItem['sku_id']]['sku_name'];
            $tmp['amount'] = $arrSkuItem['order_amount'];
            $tmp['netWeight'] = $arrMapSkuInfos[$arrSkuItem['sku_id']]['sku_net'];
            $tmp['netWeightUnit'] = $arrMapSkuInfos[$arrSkuItem['sku_id']]['sku_net_unit'];
            $tmp['upcUnit'] = $arrMapSkuInfos[$arrSkuItem['sku_id']]['min_upc']['upc_unit'];
            $tmp['specifications'] = $arrMapSkuInfos[$arrSkuItem['sku_id']]['min_upc']['upc_unit_num'];
            $tmp['back'] = Order_Define_StockoutOrder::SKUS_EVENTS_IS_BACK_MAP[$arrSkuItem['event_type']];
            $tmp['eventType'] = $arrSkuItem['event_type'];
            $list[] = $tmp;
        }
       return $list;

    }
    /**
     * 拼接sku详细信息
     * @param array $arrBatchSkuParams
     * @param integer $intOrderType
     * @param bool $ischeckBusiness
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function appendSkuInfosToSkuParams($arrBatchSkuParams, $intOrderType,$ischeckBusiness = true) {
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
                unset($arrBatchSkuParams[$intKey]);
                continue;
            }
            $intSkuId = $arrSkuItem['sku_id'];
            if (empty($arrMapSkuInfos[$intSkuId])) {
                unset($arrBatchSkuParams[$intKey]);
                Order_Exception_Collector::addException(0, $intSkuId, '', Order_Exception_Const::CONCRETE_SKU_NOT_EXIST);
                continue;
            }
            // check is active
            if (Nscm_Define_Sku::SKU_IS_ACTIVE != $arrMapSkuInfos[$intSkuId]['is_active']) {
                Order_Exception_Collector::addException(0, $intSkuId, $arrMapSkuInfos[$intSkuId]['sku_name'],
                    Order_Exception_Const::CONCRETE_SKU_NOT_OPEN);
                unset($arrBatchSkuParams[$intKey]);
                continue;
            }

            if ($ischeckBusiness) {
                // check business
                $arrBusinessInfo = $arrMapSkuInfos[$intSkuId]['sku_business_form_detail'];
                $boolBusinessIsActive = false;
                foreach ($arrBusinessInfo as $row) {
                    if (Nscm_Define_Sku::SKU_IS_ACTIVE == $row['is_active'] && $row['type'] == $intOrderType) {
                        $boolBusinessIsActive = true;
                        break;
                    }
                }
                if (!$boolBusinessIsActive) {
                    Order_Exception_Collector::addException(0, $intSkuId, $arrMapSkuInfos[$intSkuId]['sku_name'],
                        Order_Exception_Const::CONCRETE_SKU_BUSINESS_FAIL);
                    unset($arrBatchSkuParams[$intKey]);
                    continue;
                }
            }
            $arrBatchSkuParams[$intKey]['sku_name'] = $arrMapSkuInfos[$intSkuId]['sku_name'];
            $arrBatchSkuParams[$intKey]['sku_net'] = $arrMapSkuInfos[$intSkuId]['sku_net'];
            $arrBatchSkuParams[$intKey]['sku_net_unit'] = $arrMapSkuInfos[$intSkuId]['sku_net_unit'];
            $arrBatchSkuParams[$intKey]['upc_id'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_id'];
            $arrBatchSkuParams[$intKey]['upc_unit'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_unit'];
            $arrBatchSkuParams[$intKey]['upc_unit_num'] = $arrMapSkuInfos[$intSkuId]['min_upc']['upc_unit_num'];
            $arrBatchSkuParams[$intKey]['sku_effect_type'] = $arrMapSkuInfos[$intSkuId]['sku_effect_type'];
            $arrBatchSkuParams[$intKey]['sku_effect_day'] = $arrMapSkuInfos[$intSkuId]['sku_effect_day'];
            $arrBatchSkuParams[$intKey]['sku_category_text'] = $arrMapSkuInfos[$intSkuId]['sku_category_text'];
            $arrBatchSkuParams[$intKey]['sku_business_form'] = implode(',', $arrMapSkuInfos[$intSkuId]['sku_business_form']);
            $arrBatchSkuParams[$intKey]['send_price_info'] =
                $this->getSendPriceInfo($arrMapSkuInfos[$intSkuId]['sku_business_form_detail'], $intOrderType);
            $arrBatchSkuParams[$intKey]['sku_tax_rate'] = $arrMapSkuInfos[$intSkuId]['sku_tax_rate'];
            $arrBatchSkuParams[$intKey]['import'] = intval($arrMapSkuInfos[$intSkuId]['sku_from_country']);
            $arrBatchSkuParams[$intKey]['send_upc_num'] =
                $arrBatchSkuParams[$intKey]['send_price_info']['default_upc_num'];
        }
        return $arrBatchSkuParams;
    }

    /**
     * @param array $arrBusinessFormDetail
     * @param integer $intOrderType
     * @return array|mixed
     */
    protected function getSendPriceInfo($arrBusinessFormDetail, $intOrderType) {
        if (empty($arrBusinessFormDetail)) {
            return [];
        }
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

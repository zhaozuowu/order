<?php
/**
 * @name Action_GetSkuStockInfo
 * @desc 查询商品库存信息
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetLocationStock extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id' => 'int|required',
        'sku_ids'      => 'arr|required|arr_min[1]|type[int]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_adjust_GetStockInfo
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        if (!empty($this->arrReqPost['sku_ids'])) {
            $this->arrReqPost['sku_ids'] = explode(',', $this->arrReqPost['sku_ids']);
        }
        $this->objPage = new Service_Page_adjust_GetStockInfo();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult = [
        ];

        if (empty($data)) {
            return $arrFormatResult;
        }

        foreach ($data as $value) {
            $arrFormatDetail             = [];
            $arrFormatDetail['sku_id']   = empty($value['sku_id']) ? '' : strval($value['sku_id']);
            $arrFormatDetail['sku_name'] = empty($value['sku_name']) ? '' : strval($value['sku_name']);
            $arrFormatDetail['upc_id']   = empty($value['min_upc']['upc_id']) ? '' : strval($value['min_upc']['upc_id']);
            if (!empty($value['sku_net_unit']) && !empty($value['sku_net'])) {
                $arrFormatDetail['sku_net'] = $value['sku_net'] . $this->formatSkuNetUnit($value['sku_net_unit']);
            }
            $arrFormatDetail['upc_unit'] = empty($value['min_upc']['upc_unit']) ? '' : $this->formatSkuUpcUnit($value['min_upc']['upc_unit']);

            if (!empty($value['sku_batch_info'])) {
                foreach ($value['sku_batch_info'] as $arrStockDetailRet) {
                    $arrStockDetail                      = [];
                    $arrStockDetail['location_code']     = $arrStockDetailRet['location_code'];
                    $arrStockDetail['available_amount']  = !isset($arrStockDetailRet['adjustable_amount']) ? '' : strval($arrStockDetailRet['adjustable_amount']);
                    $arrStockDetail['is_defective_text'] = empty($arrStockDetailRet['is_defective_text']) ? '' : $arrStockDetailRet['is_defective_text'];

                    if (Nscm_Define_Sku::SKU_EFFECT_FROM == $value['sku_effect_type']) {
                        $arrStockDetail['production_or_expire_time'] = strtotime(date('Y-m-d',
                            $arrStockDetailRet['production_time']));
                    } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $value['sku_effect_type']) {
                        $arrStockDetail['production_or_expire_time'] = strtotime(date('Y-m-d',
                            $arrStockDetailRet['expiration_time']));
                    }

                    $arrFormatDetail['sku_stock_detail'][] = $arrStockDetail;
                }
            } else {
                $arrFormatDetail['sku_stock_detail'] = [];;
            }

            $arrFormatResult[] = $arrFormatDetail;
        }

        return $arrFormatResult;
    }

    /**
     * 格式化净含量单位
     * @param $intSkuNetUnit
     * @return string
     */
    protected function formatSkuNetUnit($intSkuNetUnit)
    {
        $strSkuNetUnit = '';

        if (!empty($intSkuNetUnit)) {
            $strSkuNetUnit = empty(Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$intSkuNetUnit]) ? '' : Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$intSkuNetUnit];
        }

        return $strSkuNetUnit;
    }

    /**
     * 格式化upc单位
     * @param $intSkuUpcUnit
     * @return string
     */
    protected function formatSkuUpcUnit($intSkuUpcUnit)
    {
        $strSkuUpcUnit = '';

        if (!empty($intSkuUpcUnit)) {
            $strSkuUpcUnit = empty(Order_Define_Sku::UPC_UNIT_MAP[$intSkuUpcUnit]) ? '' : Order_Define_Sku::UPC_UNIT_MAP[$intSkuUpcUnit];
        }

        return $strSkuUpcUnit;
    }
}
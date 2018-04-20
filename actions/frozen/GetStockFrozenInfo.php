<?php
/**
 * @name Action_GetStockFrozenInfo
 * @desc 获取仓库商品可冻结批次数据
 * @author ziliang.zhang02@ele.me
 */

class Action_GetStockFrozenInfo extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'              => 'int|required|min[1]',
        'sku_id'                    => 'int|required|min[1]',
        'is_defective'              => 'int|min[1]|max[2]',
        'sku_effect_type'           => 'int|min[1]|max[2]',
        'production_or_expiration_time' => 'int|min[1]'
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_frozen_GetStockFrozenInfo
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        if(!empty($this->arrReqPost['sku_ids'])) {
            $this->arrReqPost['sku_ids'] = explode(',', $this->arrReqPost['sku_ids']);
        }
        $this->objPage = new Service_Page_frozen_GetStockFrozenInfo();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult = [];

        if(empty($data)) {
            return $arrFormatResult;
        }

        foreach ($data as $value) {
            $arrFormatDetail = [];
            $arrFormatDetail['sku_id'] = empty($value['sku_id']) ? '' : strval($value['sku_id']);
            $arrFormatDetail['sku_name'] = empty($value['sku_name']) ? '' : strval($value['sku_name']);
            $arrFormatDetail['upc_id'] = empty($value['min_upc']['upc_id']) ? '' : strval($value['min_upc']['upc_id']);
            if(!empty($value['sku_net_unit']) && !empty($value['sku_net'])) {
                $arrFormatDetail['sku_net'] = $value['sku_net'] . $this->formatSkuNetUnit($value['sku_net_unit']);
            }
            $arrFormatDetail['upc_unit'] = empty($value['min_upc']['upc_unit']) ? '' : $this->formatSkuUpcUnit($value['min_upc']['upc_unit']);
            $arrFormatDetail['upc_unit_num'] = empty($value['min_upc']['upc_unit_num']) ? '' : '1*' . $value['min_upc']['upc_unit_num'];
            if(!empty($value['detail'])) {
                foreach ($value['detail'] as $arrStockDetailRet) {
                    $arrStockDetail = [];
                    $arrStockDetail['freezable_amount'] = !isset($arrStockDetailRet['freezable_amount']) ? '' : strval($arrStockDetailRet['freezable_amount']);
                    $arrStockDetail['is_defective_text'] = empty($arrStockDetailRet['is_defective_text']) ? '' : $arrStockDetailRet['is_defective_text'];

                    if (Nscm_Define_Sku::SKU_EFFECT_FROM == $value['sku_effect_type']) {
                        $arrStockDetail['production_or_expiration_time'] = strtotime(date('Y-m-d',
                            $arrStockDetailRet['production_time']));
                    } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $value['sku_effect_type']) {
                        $arrStockDetail['production_or_expiration_time'] = strtotime(date('Y-m-d',
                            $arrStockDetailRet['expiration_time']));
                    }

                    $arrFormatResult[] = array_merge($arrFormatDetail, $arrStockDetail);
                }
            }
        }

        return $arrFormatResult;
    }

    /**
     * 格式化净含量单位
     * @param $intSkuNetUnit
     * @return string
     */
    protected function formatSkuNetUnit($intSkuNetUnit) {
        $strSkuNetUnit = '';

        if(!empty($intSkuNetUnit)) {
            $strSkuNetUnit = empty(Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$intSkuNetUnit]) ? '' : Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$intSkuNetUnit];
        }

        return $strSkuNetUnit;
    }

    /**
     * 格式化upc单位
     * @param $intSkuUpcUnit
     * @return string
     */
    protected function formatSkuUpcUnit($intSkuUpcUnit) {
        $strSkuUpcUnit = '';

        if(!empty($intSkuUpcUnit)) {
            $strSkuUpcUnit = empty(Order_Define_Sku::UPC_UNIT_MAP[$intSkuUpcUnit]) ? '' : Order_Define_Sku::UPC_UNIT_MAP[$intSkuUpcUnit];
        }

        return $strSkuUpcUnit;
    }
}
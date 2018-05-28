<?php
/**
 * @name Service_Page_adjust_GetStockInfo
 * @desc 查询库存信息，包括可用库存、成本价
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Shift_GetLocationStock
{
    /**
     * adjust order data service
     * @var Service_Data_Stock
     */
    protected $objStock;
    protected $objSku;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStock = new Dao_Huskar_Stock();
        $this->objSku = new Dao_Ral_Sku();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $arrOutput = $this->objStock->getRemovableSkuBatchInfo($arrInput );
        if(empty($arrOutput)) return [];
        return $this->formatResult($arrOutput);
    }

    public function formatResult($arrInput){
        $skuIds = array_column($arrInput['detail'],'sku_id');
        $skuInfos = $this->objSku->getSkuInfos($skuIds);
        foreach ($arrInput['detail'] as &$value){
               $value['sku_name'] = $skuInfos[$value['sku_id']]['sku_name'];
               $value['upc_id'] = $skuInfos[$value['sku_id']]['min_upc']['display_upc_id'];
               $value['upc_unit'] = $skuInfos[$value['sku_id']]['min_upc']['upc_unit'];
               $value['upc_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$skuInfos[$value['sku_id']]['min_upc']['upc_unit']];
               $value['upc_unit_num'] = $skuInfos[$value['sku_id']]['min_upc']['upc_unit_num'];
               $value['sku_effect_type_text'] = Nscm_Define_Sku::SKU_EFFECT_TYPE_TEXT[$skuInfos[$value['sku_id']]['sku_effect_type']];
               $value['is_defective_text'] = Nscm_Define_Stock::QUALITY_TEXT_MAP[$value['is_defective']];
               if (Nscm_Define_Sku::SKU_EFFECT_FROM == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                   $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['production_time']));
               } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                   $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['expiration_time']));
               }
        }
        return $arrInput;
    }
}
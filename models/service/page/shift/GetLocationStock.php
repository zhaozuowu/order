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

        return $this->formatResult($arrOutput);
    }

    public function formatResult($arrInput){
        $skuIds = array_column($arrInput,'sku_id');
        $skuInfos = $this->objSku->getSkuInfos($skuIds);
        foreach ($arrInput as &$value){
           foreach ($skuInfos as $sku){
               if($value['sku_id'] == $sku['sku_id']){
                   $value['sku_name'] = $sku['sku_id'];
                   $value['upc_id'] = $sku['main_upc_id'];
               }
           }
        }
        return $arrInput;
    }
}
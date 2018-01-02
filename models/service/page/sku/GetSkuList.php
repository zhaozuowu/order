<?php
/**
 * @name Service_Page_Sku_GetSkuList
 * @desc get sku list
 * @author wanggang01@iwaimai.baidu.com
 */

class Service_Page_Sku_GetSkuList
{
    /**
     * sku data service
     * @var Service_Data_Sku
     */
    protected $objSku;

    /**
     * init
     */
    public function __construct()
    {
        $this->objSku = new Service_Data_Sku();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $ret = $this->objSku->getSkuList($arrInput['page_size'],
            $arrInput['sku_id'],
            $arrInput['upc_id'],
            $arrInput['sku_name'],
            $arrInput['sku_category_1'],
            $arrInput['sku_category_2'],
            $arrInput['sku_category_3'],
            $arrInput['page_num']);
        return $ret;
    }
}

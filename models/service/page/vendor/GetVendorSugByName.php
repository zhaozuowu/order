<?php
/**
 * @name Service_Page_Vendor_GetVendorSugByName
 * @desc get vendor sug by name
 * @author wanggang01@iwaimai.baidu.com
 */

class Service_Page_Vendor_GetVendorSugByName
{
    /**
     * vendor data service
     * @var Service_Data_Vendor
     */
    protected $objVendor;

    /**
     * init
     */
    public function __construct()
    {
        $this->objVendor = new Service_Data_Vendor();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $ret = $this->objVendor->getVendorSugByName($arrInput['vendor_name']);
        return $ret;
    }
}

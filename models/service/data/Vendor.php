<?php
/**
 * @name Service_Data_Vendor
 * @desc vendor service data
 * @author wanggang01@iwaimai.baidu.com
 */
class Service_Data_Vendor
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
        $this->objVendor = new Dao_Ral_Vendor();
    }

    /**
     * get vendor name suggestion by name
     * @param  string $strVendorName
     * @return array
     */
    public function getVendorSugByName($strVendorName)
    {
        $ret = $this->objVendor->getVendorSugByName($strVendorName);
        return $ret;
    }
}

<?php
/**
 * @name Service_Data_Vendor
 * @desc vendor service data
 * @author wanggang01@iwaimai.baidu.com
 */
class Service_Data_Vendor
{
    /**
     * vendor dao
     * @var Service_Data_Vendor
     */
    protected $objVendorDao;

    /**
     * init
     */
    public function __construct()
    {
        $this->objVendorDao = new Dao_Ral_Vendor();
    }

    /**
     * get vendor name suggestion by name
     * @param  string $strVendorName
     * @return array
     */
    public function getVendorSugByName($strVendorName)
    {
        $ret = [];
        if (empty($strVendorName)) {
            return $ret;
        }
        $ret = $this->objVendorDao->getVendorSugByName($strVendorName);
        return $ret;
    }
}

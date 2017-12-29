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
        $this->objVendor = new Service_Data_Vendor();
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
        $arrVendors = Model_Orm_Vendor::findRows([
            'vendor_id',
            'vendor_name',
        ], [
            'vendor_name' => [
                'like', "{$strVendorName}%"
            ],
            'is_delete' => Vendor_Define_Const::NOT_DELETE,
        ], [
            'id' => 'desc',
        ]);
        return $arrVendors;
    }
}

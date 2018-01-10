<?php
/**
 * @name Dao_Ral_Vendor
 * @desc vendor ral dao
 * @author wanggang(wanggang01@iwaimai.baidu.com)
 */

class Dao_Ral_Vendor
{
    /**
     * api raler
     * @var Order_ApiRaler
     */
    protected $objApiRal;

    /**
     * vendor sug
     * @var string
     */
    const API_RALER_VENDOR_SUG = 'getvendorsugbyname';

    /**
     * init
     */
    public function __construct()
    {
        $this->objApiRal = new Order_ApiRaler();
    }

    /**
     * get vendor name suggestion by name
     * @param $strVendorName
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getVendorSugByName($strVendorName)
    {
        $ret = [];
        if (empty($strVendorName)) {
            return $ret;
        }
        $req[self::API_RALER_VENDOR_SUG] = [
            'vendor_name' => $strVendorName,
        ];
        $ret = $this->objApiRal->getData($req);
        $ret = !empty($ret[self::API_RALER_VENDOR_SUG])?$ret[self::API_RALER_VENDOR_SUG]:[];
        return $ret;
    }
}
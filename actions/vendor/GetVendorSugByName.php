<?php
/**
 * @name Action_GetVendorSugByName
 * @desc Action_GetVendorSugByName
 * @author wanggang01@iwaimai.baidu.com
 */

class Action_GetVendorSugByName extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'vendor_name' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Vendor_GetVendorSugByName();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrRet = [];
        foreach ((array)$data as $row) {
            $arrRet[] = $row;
        }
        return ['vendor_list' => $arrRet];
    }
}
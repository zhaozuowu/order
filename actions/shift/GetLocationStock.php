<?php
/**
 * @name Action_GetSkuStockInfo
 * @desc 查询商品库存信息
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetLocationStock extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'location_code'   => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_adjust_GetStockInfo
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_GetLocationStock();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
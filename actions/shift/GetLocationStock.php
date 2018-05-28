<?php
/**
 * Class Action_GetLocationStock
 */

class Action_GetLocationStock extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'location_code'     => 'str|required',
        'page_num'          => 'int|optional|default[1]',
        'page_size'         => 'int|optional|default[50]',
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
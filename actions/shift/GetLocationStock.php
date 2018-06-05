<?php
/**
 * Class Action_GetLocationStock
 */

class Action_GetLocationStock extends Order_Base_Action
{
    protected $boolCheckWarehouse = false;
    protected $boolCheckAuth = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'                  => 'int|required',
        'location_code'                 => 'str|required',
        'sku_id'                        => 'int|min[1]',
        'is_defective'                  => 'int|min[1]|max[2]',
        'sku_effect_type'               => 'int|min[1]|max[2]',
        'production_or_expiration_time' => 'int|min[1]',
        'page_num'                      => 'int|optional|default[1]',
        'page_size'                     => 'int|optional|default[50]',
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
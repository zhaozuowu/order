<?php
/**
 * @name Action_Createincreaseorder
 * @desc 创建库存调整单-调增
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_CreateOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'source_location'   => 'str|required',
        'target_location'   => 'str|required',
        'detail'            => [
            'validate'      => 'json|required|decode',
            'type'          => 'array',
            'params'        => [
                'sku_id'                => 'int|required',
                'sku_name'              => 'str|required',
                'upc_id'                => 'str|required|min[1]|len[64]',
                'upc_unit'              => 'int|required',
                'upc_unit_num'          => 'int|required',
                'production_time'       => 'int|required',
                'expiration_time'       => 'int|required',
                'shift_amount'          => 'int|required',
                'is_defective'          => 'int|required',
            ],
        ],
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_Adjust_CreateOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Shift_CreateOrder();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult                          = [];
        $arrFormatResult['shift_order_id'] = empty($data['shift_order_id']) ? '' : Nscm_Define_OrderPrefix::SHO . intval($data['shift_order_id']);

        return $arrFormatResult;
    }
}
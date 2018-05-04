<?php
/**
 * @name Action_Createincreaseorder
 * @desc 创建库存调整单-调增
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_Createincreaseorder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'   => 'int|required',
        'warehouse_name' => 'strutf8|required|min[1]|len[64]',
        'adjust_type'    => 'int|required|max[10]',
        'remark'         => 'strutf8|required|min[1]|len[255]',
        'detail'         => [
            'validate' => 'json|required|decode',
            'type'     => 'array',
            'params'   => [
                'sku_id' => 'int|required',
                'detail' => [
                    'validate' => 'json|required|decode',
                    'type'     => 'array',
                    'params'   => [
                        'production_or_expire_time' => 'int|required',
                        'adjust_amount'             => 'int|required|min[1]',
                        'is_defective'              => 'int|required|min[1]|max[2]',
                        'location_id'               => 'str|optional|min[1]|len[64]',
                    ]
                ],
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
        $this->objPage = new Service_Page_Adjust_CreateOrder();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult                          = [];
        $arrFormatResult['stock_adjust_order_id'] = empty($data['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($data['stock_adjust_order_id']);

        return $arrFormatResult;
    }
}
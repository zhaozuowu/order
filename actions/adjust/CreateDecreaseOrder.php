<?php
/**
 * @name Action_Createdecreaseorder
 * @desc 创建库存调整单-调减
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_Createdecreaseorder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'warehouse_name'    => 'str|required',
        'adjust_type'       => 'int|required|min[10]',
        'remark'            => 'str|required',
        'detail'            => [
            'validate'              => 'json|required|decode',
            'type'                  => 'array',
            'params'                => [
                'sku_id'                    => 'int|required',
                'adjust_amount'             => 'int|required',
            ],
        ]
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
        $arrFormatResult = [];
        $arrFormatResult['stock_adjust_order_id']    = empty($data['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($data['stock_adjust_order_id']);

        return $arrFormatResult;
    }
}
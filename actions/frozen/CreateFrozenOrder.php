<?php
/**
 * @name Action_Createfrozenorder
 * @desc 创建冻结单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_Createfrozenorder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'warehouse_name'    => 'str|required',
        'remark'            => 'str|required',
        'detail'            => [
            'validate'              => 'json|required|decode',
            'type'                  => 'array',
            'params'                => [
                'sku_id'                    => 'int|required',
                'is_defective'              => 'int|required|min[1]|max[2]',
                'max_frozen_amount'         => 'int|required|min[1]',
                'frozen_amount'             => 'int|required|min[1]',
                'production_or_expire_time' => 'int|required',
                'location_code'             => 'str|required',
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
     * @var Service_Page_Frozen_CreateOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Frozen_CreateOrder();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult['stock_frozen_order_id'] = empty($data['stock_frozen_order_id'])
            ? '' : Nscm_Define_OrderPrefix::F . intval($data['stock_frozen_order_id']);

        return $arrFormatResult;
    }
}
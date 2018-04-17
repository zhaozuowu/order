<?php
/**
 * @name Action_Unfrozen
 * @desc 冻结单解冻
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_Unfrozen extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'      => 'int|required',
        'stock_frozen_order_id'     => 'regex|patern[/^(F\d{13})?$/]',
        'detail'            => [
            'validate'              => 'json|required|decode',
            'type'                  => 'array',
            'params'                => [
                'sku_id'                    => 'int|required',
                'is_defective'              => 'int|required|min[1]|max[2]',
                'current_frozen_amount'     => 'int|required|min[1]',
                'unfrozen_amount'           => 'int|required|min[1]',
                'production_or_expire_time' => 'int|required',
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
     * @var Service_Page_Frozen_Unfrozen
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Frozen_Unfrozen();
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
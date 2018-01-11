<?php
/**
 * @name Action_CreateStockinOrder
 * @desc Action_CreateStockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_CreateStockinOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'source_order_id' => 'regex|patern[/^((ASN|SOO)\d{13})?$/]',
        'warehouse_id' => 'int|required',
        //'stockin_order_type' => 'int|min[1]|max[3]',
        'stockin_order_remark' => 'strutf8',
        'sku_info_list' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required|min[1000000]|max[9999999]',
                'real_stockin_info' => [
                    'validate' => 'arr|required|decode',
                    'type' => 'array',
                    'params' => [
                        'amount' => 'int|required',
                        'expire_date' => 'int|required',
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
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockin_CreateStockinOrder();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
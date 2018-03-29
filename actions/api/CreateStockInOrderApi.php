
<?php
/**
 * @name Action_CreateStockInOrderApi
 * @desc Action_CreateStockInOrderApi
 * @author hang.song02@ele.me
 */

class Action_CreateStockInOrderApi extends Order_Base_ApiAction
{
    /**
     * 请求方式
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;
    /**
     * 验证参数
     * @var array
     */
    protected $arrInputParams = [
        'source_order_id' => 'int|required',
        'shipment_order_id' => 'int|required',
        'stockin_order_remark' => 'strutf8',
        'stockin_order_return_type' => 'int|required',
        'sku_info_list' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required|min[1000000]|max[9999999]',
                'sku_amount' => 'int|required',
            ],
        ],
    ];

    /**
     * format response
     * @param array $data
     */
    public function format($data)
    {
        return $data;
    }

    /**
     * 调用pageService层入口
     * @return mixed
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_StockIn_CreateSysStockInOrder();
    }

}
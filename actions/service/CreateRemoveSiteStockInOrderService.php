
<?php
/**
 * @name Action_CreateRemoveSiteStockInOrderApi
 * @desc Action_CreateRemoveSiteStockInOrderApi
 * @author zuowu.zhao@ele.me
 */

class Action_Service_CreateRemoveSiteStockInOrderService extends Order_Base_ServiceAction
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
        'warehouse_id' => 'int|required',
        'shipment_order_id' => 'int|required',
        'stockin_order_remark' => 'strutf8',
        'stockin_order_source' => 'int|required',
        'asset_information' => [
            'validate' => 'json|decode',
            'type' => 'array',
            'params' => [
                'device_no' => 'str',
                'device_type' => 'int',
            ],
        ],
        'customer_info'=>'json|decode|required',
        'sku_info_list' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|min[1000000]|max[9999999]',
                'sku_amount' => 'int',
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
        $this->objPage = new Service_Page_StockIn_CreateWithdrawStockInOrder();
    }

}
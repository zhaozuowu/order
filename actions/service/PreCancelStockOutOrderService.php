<?php
/**
 * @name Action_PreCancelStockOutOrderApi
 * @desc Action_PreCancelStockOutOrderApi
 * @author hang.song02@ele.me
 */

class Action_Service_PreCancelStockOutOrderService extends Order_Base_ServiceAction
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
        'stock_out_order_id' => 'int|required',
    ];

    /**
     * format response
     * @param array $data
     */
    public function format($data)
    {

    }

    /**
     * 调用pageService层入口
     * @return mixed
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_PreCancelStockOutOrder();
    }
}
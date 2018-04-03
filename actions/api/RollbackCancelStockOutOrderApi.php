<?php
/**
 * @name Action_RollBackCancelStockOutOrderApi
 * @desc Action_RollBackCancelStockOutOrderApi
 * @author bochao.lv@ele.me
 */

class Action_RollBackCancelStockOutOrderApi extends Order_Base_ApiAction
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
        $this->objPage = new Service_Page_Stockout_RollbackCancelStockOutOrder();
    }
}
<?php
/**
 * @name Action_CacelStockout
 * @desc 确认取消出库单
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_CacelStockout extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'int|required',
        'remark' => 'str',

    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_Api_CacelStockoutOrder();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
       return $data;
    }

}
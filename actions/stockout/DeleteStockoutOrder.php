<?php
/**
 * @name Action_DeleteStockoutOrder
 * @desc 作废出库单
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_DeleteStockoutOrder extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
        'mark' => 'str',
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
        $this->objPage = new Service_Page_Stockout_DeleteStockoutOrder();

        
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
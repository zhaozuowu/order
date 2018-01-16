<?php
/**
* @name Action_GetStockoutOrderSkus
* @desc 查询出库单商品列表
* @author  jinyu02@iwaimai.baidu.com
*/

class Action_GetStockoutSkus extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [

    ];

    /**
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;


    /**
     * init object
     */
    public function myConstruct() {
        //$this->objPage =
    }


    /**
     * @param array $arrRet
     */
    public function format($arrRet) {

    }
}
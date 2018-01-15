<?php
/**
<<<<<<< HEAD
 * Created by PhpStorm.
 * User: iwaimai
 * Date: 15/01/2018
 * Time: 11:30 AM
=======
 * @name 查询出库单商品列表
 * @desc
 * @author jinyu02@iwaimai.baidu.com
>>>>>>> master
 */

class Service_Page_Stockout_GetStockoutOrderSkus
{
<<<<<<< HEAD
=======
    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockoutOrder = new Service_Data_StockoutOrder();
    }

    /**
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrList = $this->objStockoutOrder->getStockoutOrderSkus($arrInput);
        $intTotal = $this->objStockoutOrder->getStockoutOrderSkusCount($arrInput);
        return [
            'total' => $intTotal,
            'skus' => $arrList,
        ];
    }
>>>>>>> master

}
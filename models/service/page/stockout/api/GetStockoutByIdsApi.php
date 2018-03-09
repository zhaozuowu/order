<?php

/**
 * @name Service_Page_Stockout_Api_GetStockoutByIdsApi
 * @desc 查询出库单明细
 * @author huabang.xue@ele.me
 */
class Service_Page_Stockout_Api_GetStockoutByIdsApi implements Order_Base_Page
{
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
     * execute
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function execute($arrInput)
    {
        $arrRet = [];
        if (empty($arrInput)) {
            return $arrRet;
        }
        $arrStockoutOrderIds = explode(',', $arrInput['stockout_order_ids']);
        $arrRet = $this->objStockoutOrder->getOrderDetailByStockoutOrderIds($arrStockoutOrderIds);
        return $arrRet;
    }
}
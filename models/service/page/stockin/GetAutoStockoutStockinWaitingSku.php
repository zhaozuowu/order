<?php
/**
 * @name Service_Page_Stockin_GetAutoStockoutStockinWaitingSku
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/4/25 18:34
 */

class Service_Page_Stockin_GetAutoStockoutStockinWaitingSku implements Order_Base_Page
{

    /**
     * function execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $objData = new Service_Data_Stockin_StockinOrder();
        return $objData->getStockinWaitingSku($arrInput['warehouse_id']);
    }
}
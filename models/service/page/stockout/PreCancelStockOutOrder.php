<?php
/**
 * @name Service_Page_Stockout_PreCancelStockOutOrder
 * @desc 预取消出库单
 * @author hang.song02@ele.me
 */

class Service_Page_Stockout_PreCancelStockOutOrder
{
    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objData;

    /**
     * init
     */
    public function __construct()
    {
        $this->objData = new Service_Data_StockoutOrder();
    }


    /**
     * execute
     * @param  array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intStockOutOrderId = $arrInput['stock_out_order_id'];
        return $this->objData->preCancelOrder($intStockOutOrderId);
    }
}
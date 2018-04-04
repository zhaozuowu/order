<?php
/**
 * @name Service_Page_Stockout_RollbackCancelStockOutOrder
 * @desc 回滚取消出库单
 * @author bochao.lv@ele.me
 */

class Service_Page_Stockout_RollbackCancelStockOutOrder
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
        return $this->objData->rollBackCancelOrder($intStockOutOrderId);
    }
}
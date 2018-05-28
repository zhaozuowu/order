<?php
/**
 * @name Service_Page_StockIn_CreateWithdrawStockInOrder
 * @desc 创建系统销退入库单(撤点)
 * @author zuowu.zhao@ele.me
 */

class Service_Page_StockIn_CreateWithdrawStockInOrder
{
    /**
     * @var Service_Data_StockoutOrder
     */
    private $objDataStockOut;
    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    private $objDataStockIn;

    public function __construct()
    {
        $this->objDataStockOut = new Service_Data_StockoutOrder();
        $this->objDataStockIn = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * @param  array $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function execute($arrInput)
    {
        $arrInput['intStockInOrderId'] = Order_Util_Util::generateStockinOrderCode();
        $this->objDataStockIn->createWithdrawStockInOrder($arrInput);
        return [
            'stockin_order_id' => $arrInput['intStockInOrderId'],
        ];

    }
}
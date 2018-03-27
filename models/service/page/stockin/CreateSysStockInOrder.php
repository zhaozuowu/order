<?php
/**
 * @name Service_Page_StockIn_CreateSysStockInOrder
 * @desc 创建系统销退入库单
 * @author hang.song02@ele.me
 */

class Service_Page_StockIn_CreateSysStockInOrder
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
     * @param $arrInput
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $intSourceOrderId = intval($arrInput['source_order_id']);
        $arrRet = $this->objDataStockOut->getOrderAndSkuListByStockoutOrderId($intSourceOrderId);
        if (empty($arrRet)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $arrSourceOrderInfo = $arrRet['stockout_order_info'];
        $arrSourceOrderSkuList = $arrRet['stockout_order_sku'];
        if (Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS == $arrSourceOrderInfo['stockout_order_status']) {
            Order_BusinessError::throwException(Order_Error_Code::INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN);
        }
        if (Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS != $arrSourceOrderInfo['stockout_order_status']) {
            Order_BusinessError::throwException(Order_Error_Code::INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN);
        }
        $this->objDataStockIn->createSysStockInOrder($arrSourceOrderSkuList, $arrSourceOrderInfo,
            $arrInput['sku_info_list'], $arrInput['stockin_order_remark']);
    }
}
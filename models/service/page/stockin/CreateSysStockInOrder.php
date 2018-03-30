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
     * @param  array $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function execute($arrInput)
    {
        $intSourceOrderId = intval($arrInput['stockout_order_id']);
        $intStockInOrderId = $this->objDataStockIn->getStockInOrderIdByStockOutId($intSourceOrderId);
        if (empty($intStockInOrderId)) {
            $arrRet = $this->objDataStockOut->getOrderAndSkuListByStockoutOrderId($intSourceOrderId);
            if (empty($arrRet)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
            }
            $intStockInOrderId = Order_Util_Util::generateStockinOrderCode();
            $arrSourceOrderInfo = $arrRet['stockout_order_info'];
            $arrSourceOrderSkuList = $arrRet['stockout_order_sku'];
            if (Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS == $arrSourceOrderInfo['stockout_order_status']) {
                Order_BusinessError::throwException(Order_Error_Code::INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN);
            }
            if (Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS != $arrSourceOrderInfo['stockout_order_status']) {
                Order_BusinessError::throwException(Order_Error_Code::INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN);
            }
            $this->objDataStockIn->createSysStockInOrder($intStockInOrderId, $arrSourceOrderSkuList, $arrSourceOrderInfo,
                $arrInput['shipment_order_id'], $arrInput['sku_info_list'], $arrInput['stockin_order_remark'],
                $arrInput['stockin_order_return_type']);
            $this->objDataStockIn->setStockInOrderIdByStockOutId($intSourceOrderId, $intStockInOrderId);
        }

        return [
            'stockin_order_id' => $intStockInOrderId,
        ];

    }
}
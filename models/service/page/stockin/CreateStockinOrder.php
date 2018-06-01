<?php
/**
 * @name Service_Page_Stockin_CreateStockinOrder
 * @desc Service_Page_Stockin_CreateStockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Stockin_CreateStockinOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    private $objDataStockin;

    /**
     * @var Service_Data_StockoutOrder
     */
    private $objDataStockout;

    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataReserve;

    private $objWarehouse;

    /**
     * Service_Page_Stockin_CreateStockinOrder constructor.
     */
    function __construct()
    {
        $this->objDataStockin = new Service_Data_Stockin_StockinOrder();
        $this->objDataStockout = new Service_Data_StockoutOrder();
        $this->objDataReserve = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * execute
     * @param array $arrInput
     * @return void
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function execute($arrInput)
    {
        if (preg_match('/^(SOO|ASN)(\d{13})$/', $arrInput['source_order_id'], $matches)) {
            if (Nscm_Define_OrderPrefix::ASN == $matches[1]) {
                $intType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
            } else {
                $intType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
            }
            $intSourceOrderId = intval($matches[2]);
        } else {
            Order_Error::throwException(Order_Error_Code::SOURCE_ORDER_TYPE_ERROR);
        }
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intType) {
            $arrSourceOrderInfo = $this->objDataReserve->getReserveOrderInfoByReserveOrderId($intSourceOrderId);
            $arrSourceOrderSkus = $this->objDataReserve->getReserveOrderSkuListAll($arrSourceOrderInfo['reserve_order_id']);
            // check warehouse
            if ($arrInput['warehouse_id'] != $arrSourceOrderInfo['warehouse_id']) {
                Order_BusinessError::throwException(Order_Error_Code::WAREHOUSE_NOT_MATCH);
            }
            // check status
            if (!isset(Order_Define_ReserveOrder::ALLOW_STOCKIN[$arrSourceOrderInfo['reserve_order_status']])) {
                Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN);
            }

        } else {
            $arrRet = $this->objDataStockout->getOrderAndSkuListByStockoutOrderId($intSourceOrderId);
            $arrSourceOrderInfo = $arrRet['stockout_order_info'];
            $arrSourceOrderSkus = $arrRet['stockout_order_sku'];
            // check status
            if (!isset(Order_Define_StockoutOrder::ALLOW_STOCKIN[$arrSourceOrderInfo['stockout_order_status']])) {
                Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN);
            }
        }
        if (empty($arrSourceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $intWarehouseId = $arrInput['warehouse_id'];
        $strStockinOrderRemark = $arrInput['stockin_order_remark'];
        $arrSkuInfoList = $arrInput['sku_info_list'];
        $intCreatorId = $arrInput['_session']['user_id'];
        $strCreatorName = $arrInput['_session']['user_name'];
        $boolIgnoreCheckDate = $arrInput['ignore_check_date'];
        $intStockinDevice = $arrInput['stockin_device'];
        $intStockinOrderId = $this->objDataStockin->createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId,
            $strStockinOrderRemark, $arrSkuInfoList, $intCreatorId, $strCreatorName, $intType, $boolIgnoreCheckDate,
            $intStockinDevice);
        $arrCmdInput['stockin_order_ids'] = strval($intStockinOrderId);
        $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_PLACE_ORDER_CREATE, $arrCmdInput);
        if (false == $ret) {
            Bd_Log::warning("send wmq failed arrInput[%s] cmd[%s]",
                json_encode($arrInput), Order_Define_Cmd::CMD_PLACE_ORDER_CREATE);
        }
    }
}
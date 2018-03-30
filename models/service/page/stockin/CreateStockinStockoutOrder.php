<?php
/**
 * @name Service_Page_Stockin_CreateStockinStockoutOrder
 * @desc 手动创建销退入库单
 * @author chenwende@iwaimai.baidu.com
 */
class Service_Page_Stockin_CreateStockinStockoutOrder implements Order_Base_Page
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
    }

    /**
     * execute
     * @param array $arrInput
     * @return array|void
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function execute($arrInput)
    {
        $intSourceOrderId = null;
        $intType = null;
        if (preg_match('/^(SOO)(\d{13})$/', $arrInput['source_order_id'], $matches)) {
            if (Nscm_Define_OrderPrefix::SOO == $matches[1]) {
                $intType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT;
                $intSourceOrderId = intval($matches[2]);
            } else {
                Order_Error::throwException(Order_Error_Code::SOURCE_ORDER_TYPE_ERROR);
            }
        }

        if(empty($intSourceOrderId) || empty($intType)){
            Order_Error::throwException(Order_Error_Code::PARAM_ERROR);
        }

        if(empty($arrInput['sku_info_list'])){
            Order_BusinessError::throwException(Order_Error_Code::NWMS_SKU_LIST_EMPTY);
        }

        $arrRet = $this->objDataStockout->getOrderAndSkuListByStockoutOrderId($intSourceOrderId);
        $arrSourceOrderInfo = $arrRet['stockout_order_info'];
        $arrSourceOrderSkus = $arrRet['stockout_order_sku'];
        // check status
        if (!isset(Order_Define_StockoutOrder::ALLOW_STOCKIN[$arrSourceOrderInfo['stockout_order_status']])) {
            Order_BusinessError::throwException(Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN);
        }

        if (empty($arrSourceOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $intWarehouseId = $arrInput['warehouse_id'];
        $strStockinOrderRemark = $arrInput['stockin_order_remark'];
        $arrSkuInfoList = $arrInput['sku_info_list'];
        $intCreatorId = $arrInput['_session']['user_id'];
        $strCreatorName = $arrInput['_session']['user_name'];
        $this->objDataStockin->createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId,
            $strStockinOrderRemark, $arrSkuInfoList, $intCreatorId, $strCreatorName, $intType);
    }
}
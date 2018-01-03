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
     * @return int
     * @throws Order_BusinessError
     * @throws Wm_Orm_Error
     * @throws Exception
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
            $arrSourceOrderInfo = $this->objDataReserve->getReserveOrderInfoByPurchaseOrderId($intSourceOrderId);
            $arrSourceOrderSkus = $this->objDataReserve->getReserveOrderSkuListAll($arrSourceOrderInfo['reserve_order_id']);

        } else {
            $arrRet = $this->objDataStockout->getOrderAndSkuListByStockoutOrderId($intSourceOrderId);
            $arrSourceOrderInfo = $arrRet['stockout_order_info'];
            $arrSourceOrderSkus = $arrRet['stockout_order_sku'];
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
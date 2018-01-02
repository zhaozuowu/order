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
     */
    public function execute($arrInput)
    {
        $intType = intval($arrInput['stockin_order_type']);
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intType) {
            $arrSourceOrderInfo = $this->objDataReserve->getReserveOrderInfoByReserveOrderId($arrInput['source_order_id']);
            if (empty($arrSourceOrderInfo)) {
                // @todo source order id not exist
                Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
            }
            $arrSourceOrderSkus = $this->objDataReserve->getReserveOrderSkuListAll(
                Order_Util::trimReserveOrderIdPrefix($arrInput['source_order_id']));

        } else {

        }
        $intWarehouseId = $arrInput['warehouse_id'];
        // @todo
        $strWarehouseName = '';
        $strStockinOrderRemark = $arrInput['stockin_order_remark'];
        $arrSkuInfoList = $arrInput['sku_info_list'];
        $this->objDataStockin->createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId, $strWarehouseName,
            $strStockinOrderRemark, $arrSkuInfoList, $intCreatorId, $strCreatorName, $intType);
    }
}
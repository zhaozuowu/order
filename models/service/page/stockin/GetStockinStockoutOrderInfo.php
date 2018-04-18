<?php

/**
 * @name Service_Page_Stockin_GetStockinStockoutOrderInfo
 * @desc 获取销退入库单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Service_Page_Stockin_GetStockinStockoutOrderInfo implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_StockinOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Reserve_GetReserveOrderDetail constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strStockinOrderId = strval($arrInput['stockin_order_id']);
        $arrRetInfo = $this->objServiceData->getStockinOrderInfoByStockinOrderId($strStockinOrderId);

        $objServiceDataStockout = new Service_Data_StockoutOrder();
        $strWarehouseId = $arrRetInfo['warehouse_id'];
        $strWarehouseAddr = $objServiceDataStockout->getWarehouseAddrById($strWarehouseId);
        $arrRetInfo['warehouse_address'] = $strWarehouseAddr;

        $arrRetSku = $this->objServiceData->getStockinOrderSkus($strStockinOrderId);

        if(empty($arrRetInfo)){
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_QUERY_RESULT_EMPTY);
        }
        if(empty($arrRetSku)){
            Order_BusinessError::throwException(Order_Error_Code::NWMS_SKU_LIST_EMPTY);
        }

        $arrRet = $arrRetInfo;
        $arrRet['skus_list_info'] = $arrRetSku;

        return $arrRet;
    }
}

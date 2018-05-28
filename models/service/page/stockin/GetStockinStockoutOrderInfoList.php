<?php

/**
 * @name Service_Page_Stockin_GetStockinStockoutOrderInfoList
 * @desc 获取销退入库单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Service_Page_Stockin_GetStockinStockoutOrderInfoList implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_Stockin_StockinOrder
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
        $arrRetInfo = [];
        $strStockinOrderIds = strval($arrInput['stockin_order_ids']);
        // 转换为无前缀数组
        $arrStockinOrderIds = [];
        $arrStockinOrderPrefixIds = explode(',', $strStockinOrderIds);
        foreach($arrStockinOrderPrefixIds as $prefixId) {
            $arrStockinOrderIds[] = intval(Order_Util::trimStockinOrderIdPrefix($prefixId));
        }
        // 批量查询基础入库单信息
        $arrOrderInfo = $this->objServiceData->getStockinOrderInfoByStockinOrderIds($arrStockinOrderIds);

        // 查询添加对应仓库地址信息
        $arrWarehouseIds = [];
        foreach ($arrOrderInfo as $orderInfo) {
            // 仓库编号唯一，故去重复
            $arrWarehouseIds[$orderInfo['warehouse_id']] = $orderInfo['warehouse_id'];
        }
        $objServiceDataStockout = new Service_Data_StockoutOrder();
        $arrWarehouseAddr = $objServiceDataStockout->getWarehouseAddrByIds($arrWarehouseIds);
        $arrStockinOrderInfo = [];
        foreach ($arrOrderInfo as $orderInfo) {
            $orderInfo['warehouse_address'] = isset($arrWarehouseAddr[$orderInfo['warehouse_id']])
                ? $arrWarehouseAddr[$orderInfo['warehouse_id']]
                : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrStockinOrderInfo[] = $orderInfo;
        }

        // 批量查询添加对应商品信息
        $arrOrderListSkus = $this->objServiceData->getBatchStockinOrderSkus($arrStockinOrderIds);
//        if(empty($arrOrderListSkus['list'])){
//            Order_BusinessError::throwException(Order_Error_Code::NWMS_SKU_LIST_EMPTY);
//        }

        foreach ($arrStockinOrderInfo as $stockinOrderInfo) {
            $stockinOrderInfo['skus_list_info'] = isset($arrOrderListSkus['list'][$stockinOrderInfo['stockin_order_id']])
                ? $arrOrderListSkus['list'][$stockinOrderInfo['stockin_order_id']]
                : [];
            $arrRetInfo[] = $stockinOrderInfo;
        }

        if(empty($arrRetInfo)){
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_QUERY_RESULT_EMPTY);
        }

        return $arrRetInfo;
    }
}

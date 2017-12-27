<?php

/**
 * @name Service_Data_PurchaseOrder
 * @desc purchase order data service, 采购订单Page的Data Page
 * @author chenwende
 */
class Service_Data_PurchaseOrder
{
    /**
     * 查询采购单列表
     *
     * @param $strPurchaseOrderStatus
     * @param $strWarehouseId
     * @param $strPurchaseOrderId
     * @param $intVendorId
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     */
    public function getPurchaseOrderList($strPurchaseOrderStatus,
                                         $strWarehouseId,
                                         $strPurchaseOrderId,
                                         $intVendorId,
                                         $arrCreateTime,
                                         $arrOrderPlanTime,
                                         $arrStockinTime,
                                         $intPageNum,
                                         $intPageSize)
    {
        $arrCreateTime['start'] = intval($arrCreateTime['start']);
        $arrCreateTime['end'] = intval($arrCreateTime['end']);

        $arrOrderPlanTime['start'] = intval($arrOrderPlanTime['start']);
        $arrOrderPlanTime['end'] = intval($arrOrderPlanTime['end']);

        $arrStockinTime['start'] = intval($arrStockinTime['start']);
        $arrStockinTime['end'] = intval($arrStockinTime['end']);

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrCreateTime['start'],
                $arrCreateTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrOrderPlanTime['start'],
                $arrOrderPlanTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrStockinTime['start'],
                $arrStockinTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        $intPurchaseOrderId = intval(Order_Util::trimPurchaseIdQuotation($strPurchaseOrderId));
        $arrPurchaseOrderStatus = Order_Util::extractIntArray($strPurchaseOrderStatus);
        $arrWarehouseId  = Order_Util::extractIntArray($strWarehouseId);

        return Model_Orm_PurchaseOrder::getPurchaseOrderList(
            $arrPurchaseOrderStatus,
            $arrWarehouseId,
            $intPurchaseOrderId,
            $intVendorId,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize
        );
    }
}

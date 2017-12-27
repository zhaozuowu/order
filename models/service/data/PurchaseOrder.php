<?php

/**
 * @name Service_Data_PurchaseOrder
 * @desc purchase order data service, 采购订单Page的Data Page
 * @author chenwende
 */
class Service_Data_PurchaseOrder
{
    /**
     * Service_Data_PurchaseOrder constructor.
     */
    public function __construct()
    {
    }

    /**
     * 查询采购单列表
     *
     * @param $strPurchaseOrderStatus
     * @param $arrWarehouseId
     * @param $intPurchaseOrderId
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
                                         $arrWarehouseId,
                                         $intPurchaseOrderId,
                                         $intVendorId,
                                         $arrCreateTime,
                                         $arrOrderPlanTime,
                                         $arrStockinTime,
                                         $intPageNum,
                                         $intPageSize)
    {
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

        // check and erect query default end time to now
        if (empty($arrCreateTime['end'])) {
            $arrCreateTime['end'] = Order_Util::getNowUnixDateTime();
        }

        if (empty($arrOrderPlanTime['end'])) {
            $arrOrderPlanTime['end'] = Order_Util::getNowUnixDateTime();
        }

        if (empty($arrStockinTime['end'])) {
            $arrStockinTime['end'] = Order_Util::getNowUnixDateTime();
        }

        $arrCreateTime['start'] = intval($arrCreateTime['start']);
        $arrOrderPlanTime['start'] = intval($arrOrderPlanTime['start']);
        $arrStockinTime['start'] = intval($arrStockinTime['start']);

        $arrPurchaseOrderStatus = $this->disassemblyOrderStatus($strPurchaseOrderStatus);

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

    /**
     * 分解订单状态参数为数组
     * @param $strState
     * @return array
     */
    private function disassemblyOrderStatus($strState)
    {
        // 默认查询所有状态
        if (empty(trim($strState))) {
            return null;
        }

        // parse array
        $arrState = explode(',', $strState);
        return $arrState;
    }
}

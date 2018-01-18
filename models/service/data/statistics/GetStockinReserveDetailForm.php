<?php
/**
 * @name Service_Data_Statistics_GetStockinReserveDetailForm
 * @desc 报表服务-采购入库详细
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Statistics_GetStockinReserveDetailForm
{
    /**
     * 报表-获取采购入库明细（分页）
     *
     * @param $strWarehouseId
     * @param $strStockinOrderId
     * @param $strSourceOrderId
     * @param $intSkuId
     * @param $intVendorId
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockinReserveDetailForm(
        $strWarehouseId,
        $strStockinOrderId,
        $strSourceOrderId,
        $intSkuId,
        $intVendorId,
        $arrOrderPlanTime,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        if(empty($strWarehouseId)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrWarehouseId = Order_Util::extractIntArray($strWarehouseId);

        // 解析整形单号部分
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));
        $intSourceOrderId = intval(Order_Util::trimReserveOrderIdPrefix($strSourceOrderId));

        $arrOrderPlanTime['start'] = intval($arrOrderPlanTime['start']);
        $arrOrderPlanTime['end'] = intval($arrOrderPlanTime['end']);

        $arrStockinTime['start'] = intval($arrStockinTime['start']);
        $arrStockinTime['end'] = intval($arrStockinTime['end']);

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

        return Model_Orm_StockinReserveDetail::getStockinReserveDetail(
            $arrWarehouseId,
            $intStockinOrderId,
            $intSourceOrderId,
            $intSkuId,
            $intVendorId,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }
}
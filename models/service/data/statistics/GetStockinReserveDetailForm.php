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
     * @param $intOutputType
     * @param $strWarehouseId
     * @param $strStockinOrderId
     * @param $strSourceOrderId
     * @param $intSkuId
     * @param $intSkuCategory3
     * @param $intVendorId
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function getStockinReserveDetailForm(
        $intOutputType,
        $strWarehouseId,
        $strStockinOrderId,
        $strSourceOrderId,
        $intSkuId,
        $intSkuCategory3,
        $intVendorId,
        $arrOrderPlanTime,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        // 判断output_type类型参数是否合法
        if (!isset(Order_Define_StatisticsForm::ALL_STATUS[$intOutputType])) {
            Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        // 如果未发送后台，执行后台操作
        if (Order_Define_StatisticsForm::OUTPUT_TYPE_DISPATCH_TASK === $intOutputType) {
echo 'not implemented';
exit(0);
        }

        // output_type为立即展示时，必填页面大小参数
        if (Order_Define_StatisticsForm::OUTPUT_TYPE_DISPLAY_INSTANTLY === $intOutputType) {
            if (empty($intPageSize)) {
                Order_Error::throwException(Order_Error_Code::PARAMS_ERROR);
            }
        }

        // 解析整形单号部分
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));
        $intSourceOrderId = Order_Util::parseSourceOrderId($strSourceOrderId);

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

        $arrWarehouseId = Order_Util::extractIntArray($strWarehouseId);

        return Model_Orm_StockinReserveDetail::getStockinReserveDetail(
            $arrWarehouseId,
            $intStockinOrderId,
            $intSourceOrderId,
            $intSkuId,
            $intSkuCategory3,
            $intVendorId,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }


}
<?php
/**
 * @name Service_Data_Statistics_GetStockoutStockinDetailForm
 * @desc 获取销退入库明细（分页）
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Statistics_GetStockoutStockinDetailForm
{
    /**
     * 获取销退入库明细（分页）
     *
     * @param $strWarehouseId
     * @param $strStockinOrderId
     * @param $strSourceOrderId
     * @param $intSkuId
     * @param $intClientId
     * @param $arrStockinTime
     * @param $intPageNum
     * @param $intPageSize
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getStockoutStockinDetailForm(
        $strWarehouseId,
        $strStockinOrderId,
        $strSourceOrderId,
        $intSkuId,
        $intClientId,
        $arrStockinTime,
        $intPageNum,
        $intPageSize)
    {
        $arrWarehouseId = Order_Util::extractIntArray($strWarehouseId);

        // 解析整形单号部分
        $intStockinOrderId = intval(Order_Util::trimStockinOrderIdPrefix($strStockinOrderId));
        $intSourceOrderId = intval(Order_Util::trimStockoutOrderIdPrefix($strSourceOrderId));

        $arrStockinTime['start'] = intval($arrStockinTime['start']);
        $arrStockinTime['end'] = intval($arrStockinTime['end']);

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrStockinTime['start'],
                $arrStockinTime['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }

        return Model_Orm_StockinStockoutDetail::getStockoutStockinDetail(
            $arrWarehouseId,
            $intStockinOrderId,
            $intSourceOrderId,
            $intSkuId,
            $intClientId,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }
}
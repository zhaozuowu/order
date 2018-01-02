<?php
/**
 * @name Service_Data_Stockin_StockinOrder
 * @desc Service_Data_Stockin_StockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Stockin_StockinOrder
{
    /**
     * calculate stock in order sku info
     * @param int $intStockinOrderId
     * @param array $sourceOrderSkuInfo
     * @param array $arrSkuInfo
     * @throws Order_BusinessError
     * @throws Order_Error
     * @return array
     */
    private function formatStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo, $intOrderType = 1)
    {
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intOrderType) {
            $intSkuPrice = $sourceOrderSkuInfo['sku_price'];
            $intSkuPriceTax = $sourceOrderSkuInfo['sku_price_tax'];
            $intPlanAmount =  $sourceOrderSkuInfo['reserve_order_sku_plan_amount'];
        } else {
            $intSkuPrice = $sourceOrderSkuInfo['send_price'];
            $intSkuPriceTax = $sourceOrderSkuInfo['send_price_tax'];
            $intPlanAmount = $sourceOrderSkuInfo['pickup_amount'];
        }
        $arrDbStockinOrderSkuExtraInfo = [];
        // amount
        $intTotalAmount = 0;
        $i = 0;
        foreach ($arrSkuInfo['real_stockin_info'] as $arrRealStockinInfo) {
            $arrDbStockinOrderSkuExtraInfo[] = [
                'amount' => $arrRealStockinInfo['amount'],
                'expire_date' => $arrRealStockinInfo['expire_date'],
            ];
            $i++;
            $intTotalAmount += intval($arrRealStockinInfo['amount']);
            if ($i >= Order_Define_StockinOrder::STOCKIN_SKU_EXP_DATE_MAX) {
                // @todo stock in info too much
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
        }
        if ($intTotalAmount > $intPlanAmount) {
            // @todo stock in order sku amount must smaller than reserve order
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        return [
            'stockin_order_id' => $intStockinOrderId,
            'sku_id' => $sourceOrderSkuInfo['sku_id'],
            'upc_id' => $sourceOrderSkuInfo['upc_id'],
            'upc_unit' => $sourceOrderSkuInfo['upc_unit'],
            'upc_unit_num' => $sourceOrderSkuInfo['upc_unit_num'],
            'sku_name' => $sourceOrderSkuInfo['sku_name'],
            'sku_net' => $sourceOrderSkuInfo['sku_net'],
            'sku_net_unit' => $sourceOrderSkuInfo['sku_net_unit'],
            'sku_net_gram' => $sourceOrderSkuInfo['sku_net_gram'],
            'sku_price' => $intSkuPrice,
            'sku_price_tax' => $intSkuPriceTax,
            'stockin_order_sku_total_price' => $intTotalAmount * $intSkuPrice,
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $intSkuPriceTax,
            'reserve_order_sku_plan_amount' => $intPlanAmount,
            'stockin_order_sku_real_amount' => $intTotalAmount,
            'stockin_order_sku_extra_info' => json_encode($arrDbStockinOrderSkuExtraInfo),
        ];
    }

    /**
     * get db stock in skus
     * @param int $intStockinOrderId
     * @param array $arrReserveOrderSkus
     * @param array $arrSkuInfoList
     * @return array
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    private function getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList)
    {
        // pre treat sku
        $arrHashReserveOrderSkus = [];
        foreach ($arrReserveOrderSkus as $arrSku) {
            $arrHashReserveOrderSkus[$arrSku['sku_id']] = $arrSku;
        }
        $arrDbSkuInfoList = [];
        foreach ($arrSkuInfoList as $arrSkuInfo) {
            if (!isset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']])) {
                // @todo sku id not in purchase order or sku id repeat
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
            $arrReserveOrderSku = $arrHashReserveOrderSkus[$arrSkuInfo['sku_id']];
            $arrSkuRow = $this->formatStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo);
            $arrDbSkuInfoList[] = $arrSkuRow;
            unset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']]);
        }
        return $arrDbSkuInfoList;
    }

    /**
     * calculate total sku amount
     * @param array $arrDbSkus
     * @return int
     */
    private function calculateTotalSkuAmount($arrDbSkus)
    {
        $intResult = 0;
        foreach ($arrDbSkus as $arrSku) {
            $intResult += intval($arrSku['stockin_order_sku_real_amount']);
        }
        return $intResult;
    }

    /**
     * @param $arrSourceOrderInfo
     * @param $arrSourceOrderSkus
     * @param $intWarehouseId
     * @param $strWarehouseName
     * @param $strStockinOrderRemark
     * @param $arrSkuInfoList
     * @param $intCreatorId
     * @param $strCreatorName
     * @param $intType
     * @return int
     * @throws Exception
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function createStockinOrder($arrSourceOrderInfo, $arrSourceOrderSkus, $intWarehouseId, $strWarehouseName,
                                       $strStockinOrderRemark, $arrSkuInfoList,$intCreatorId, $strCreatorName,
                                       $arrSourceInfo, $intType)
    {

        if (!isset(Order_Define_StockinOrder::STOCKIN_ORDER_TYPES[$intType])) {
            // @todo order type error
            Order_Error::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $intStockinOrderId = Order_Util_Util::generateStockinOrderCode();
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrSourceOrderSkus, $arrSkuInfoList);
        $intStockinOrderRealAmount = $this->calculateTotalSkuAmount($arrDbSkuInfoList);
        $intStockinOrderType = intval($intType);
        if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockinOrderType) {
            $intSourceOrderId = intval($arrSourceOrderInfo['reserve_order_id']);
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['reserve_order_plan_amount'];
        } else {
            $intSourceOrderId = intval($arrSourceOrderInfo['stockout_order_id']);
            $intStockinOrderPlanAmount = $arrSourceOrderInfo['stockout_order_pickup_amount'];
        }
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH;
        $intWarehouseId = intval($intWarehouseId);
        $strWarehouseName = strval($strWarehouseName);
        $intStockinTime = time();
        $intStockinOrderCreatorId = intval($intCreatorId);
        $strStockinOrderCreatorName = strval($strCreatorName);
        $strStockinOrderRemark = strval($strStockinOrderRemark);
        Model_Orm_StockinOrder::getConnection()->transaction(function() use($intStockinOrderId, $intStockinOrderType,
            $intSourceOrderId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $strWarehouseName, $intStockinTime,
            $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
            $strStockinOrderRemark, $arrDbSkuInfoList) {
            Model_Orm_StockinOrder::createStockinOrder(
                $intStockinOrderId, $intStockinOrderType,
                $intSourceOrderId, $strSourceInfo, $intStockinOrderStatus, $intWarehouseId, $strWarehouseName, $intStockinTime,
                $intStockinOrderPlanAmount, $intStockinOrderRealAmount, $intStockinOrderCreatorId, $strStockinOrderCreatorName,
                $strStockinOrderRemark);
            Model_Orm_StockinOrderSku::batchCreateStockinOrderSku($arrDbSkuInfoList, $intStockinOrderId);
            // @todo event track
            if (!$this->notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $arrDbSkuInfoList)){
                Order_Error::throwException(Order_Error_Code::ERR__RAL_ERROR);
            }
            // @todo async notify nscm
            return $intStockinOrderId;
        });
        return $intStockinOrderId;
    }

    /**
     * call stock
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intWarehouseId
     * @param array $arrDbSkuInfoList
     * @return bool
     */
    public function notifyStock($intStockinOrderId, $intStockinOrderType, $intWarehouseId, $arrDbSkuInfoList){
        return true;
    }
}
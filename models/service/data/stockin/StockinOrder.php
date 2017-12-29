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
     * @return array
     */
    private function calculateStockinOrderSkuInfo($intStockinOrderId, $sourceOrderSkuInfo, $arrSkuInfo)
    {
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
        if ($intTotalAmount > $sourceOrderSkuInfo['reserve_order_sku_plan_amount']) {
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
            'sku_price' => $sourceOrderSkuInfo['sku_price'],
            'sku_price_tax' => $sourceOrderSkuInfo['sku_price_tax'],
            'stockin_order_sku_total_price' => $intTotalAmount * $sourceOrderSkuInfo['sku_price'],
            'stockin_order_sku_total_price_tax' => $intTotalAmount * $sourceOrderSkuInfo['sku_price_tax'],
            'reserve_order_sku_plan_amount' => $sourceOrderSkuInfo['reserve_order_sku_plan_amount'],
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
            $arrSkuRow = $this->calculateStockinOrderSkuInfo($intStockinOrderId, $arrReserveOrderSku, $arrSkuInfo);
            $arrDbSkuInfoList[] = $arrSkuRow;
            unset($arrHashReserveOrderSkus[$arrSkuInfo['sku_id']]);
        }
        return $arrDbSkuInfoList;
    }

    /**
     * calculate total sku amount
     * @param array $arrSkus
     * @return int
     */
    private function calculateTotalSkuAmount($arrSkus)
    {
        $intResult = 0;
        foreach ($arrSkus as $arrSkus) {
            $intResult += intval($arrSkus['stockin_order_sku_real_amount']);
        }
        return $intResult;
    }

    /**
     * @param $arrReserveOrderInfo
     * @param $arrReserveOrderSkus
     * @param $intWarehouseId
     * @param $strStockinOrderRemark
     * @param $arrSkuInfoList
     * @param $intCreatorId
     * @param $strCreatorName
     * @return int
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function createStockinOrderReserve($arrReserveOrderInfo, $arrReserveOrderSkus, $intWarehouseId, $strStockinOrderRemark, $arrSkuInfoList,
        $intCreatorId, $strCreatorName)
    {
        $intStockinOrderId = Order_Util_Util::generateStockinOrderCode();
        $arrDbSkuInfoList = $this->getDbStockinSkus($intStockinOrderId, $arrReserveOrderSkus, $arrSkuInfoList);
        $intStockinOrderRealAmount = $this->calculateTotalSkuAmount($arrDbSkuInfoList);
        $intStockinOrderType = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE;
        $intSourceOrderId = intval($arrReserveOrderInfo['reserve_order_id']);
        //@todo
        $arrSourceInfo = [];
        $strSourceInfo = json_encode($arrSourceInfo);
        $intStockinOrderStatus = Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH;
        $intWarehouseId = intval($intWarehouseId);
        //@todo
        $strWarehouseName = '';
        $intStockinTime = time();
        $intStockinOrderPlanAmount = $arrReserveOrderInfo['reserve_order_plan_amount'];
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
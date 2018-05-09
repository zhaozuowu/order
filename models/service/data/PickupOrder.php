<?php
/**
 * @name Service_Data_PickupOrder
 * @desc 拣货单操作类
 * @author zhaozuowu@iwaimai.baidu.com　
 */
class Service_Data_PickupOrder
{
    /**
     * init
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();
        $this->objOrmSku = new Model_Orm_StockoutOrderSku();
        $this->objStockoutOrder = new Service_Data_StockoutOrder();
    }

    /**
     * 创建拣货单
     * @param $arrStockoutOrderIds
     * @param $pickupOrderType
     * @return array
     * @throws Order_BusinessError
     */
    public function createPickupOrder($arrStockoutOrderIds, $pickupOrderType,$userId,$userName)
    {
        $res = ['failStockoutOrderIds'=>0,'sucessStockoutOrderIds'=>0];
        if (!array_key_exists($pickupOrderType,Order_Define_PickupOrder::PICKUP_ORDER_TYPE_MAP)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR,'参数异常');
        }
        $totalPickupOrderNum = count($arrStockoutOrderIds);
        $arrStockoutOrderIds = $this->objStockoutOrder->batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds);
        $arrConditions = [
            'stockout_order_id' => ['in', $arrStockoutOrderIds],
        ];
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $stockoutOrderList= $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        if (empty($stockoutOrderList)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        if (count($stockoutOrderList) != $totalPickupOrderNum) {
            $originStockoutIds = array_column($stockoutOrderList,'stockout_order_id');
            $failStockoutOrderIds = array_diff($arrStockoutOrderIds,$originStockoutIds);
            $res['failStockoutOrderIds'] = $failStockoutOrderIds;
            $arrStockoutOrderIds = $originStockoutIds;
        }
        $stockoutOrderList = $this->filterPickupIsCreated($stockoutOrderList);
        if (empty($stockoutOrderList)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_PICKUP_ORDER_IS_CREATED);
        }
        if(count($stockoutOrderList) != count($arrStockoutOrderIds)) {
            $originStockoutIds = array_column($stockoutOrderList,'stockout_order_id');
            $failStockoutOrderIds = array_diff($arrStockoutOrderIds,$originStockoutIds);
            $res['failStockoutOrderIds'] = array_merge($res['failStockoutOrderIds'],$failStockoutOrderIds);
            $arrStockoutOrderIds = $originStockoutIds;
        }
        $warehouseIds = array_column($stockoutOrderList,'warehouse_id');
        $warehouseIds = array_unique($warehouseIds);
        if(empty($warehouseIds) || count($warehouseIds) > 1) {
            Order_BusinessError::throwException(Order_Error_Code::INVALID_STOCKOUT_ORDER_WAREHOUSE_NOT_CREATE_PICKUP_ORDER);
        }
        $stockoutOrderList = array_column($stockoutOrderList,null,'stockout_order_id');
        Model_Orm_PickupOrder::getConnection()->transaction(function () use ($stockoutOrderList,$arrStockoutOrderIds,$pickupOrderType,$userId,$userName,$arrStockoutOrderIds) {
            $arrStockoutPickOrderData = $this->getCreateStockoutPickupOrderData($arrStockoutOrderIds,$pickupOrderType);
            Model_Orm_StockoutPickupOrder::batchInsert($arrStockoutPickOrderData, false);
            $arrPickupOrderData  = $this->getCreatePickupOrderData($arrStockoutPickOrderData,$stockoutOrderList,$pickupOrderType,$userId,$userName);
            Model_Orm_PickupOrder::batchInsert($arrPickupOrderData, false);
//            $arrPickupOrderSkuData = $this->getCreatePickupOrderSkuData($arrStockoutPickOrderData,$stockoutOrderList,$pickupOrderType);
            $updateData = [
                'is_pickup_ordered' => Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED,
            ];
            $arrConditions = [
                'stockout_order_id' => ['in', $arrStockoutOrderIds],
            ];
            $result = $this->objOrmStockoutOrder->updateDataByConditions($arrConditions, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
            }
        });
    }
    /**
     * @param $stockoutOrderList
     * @return array
     */
    private function filterPickupIsCreated($stockoutOrderList)
    {
        $list = [];
        foreach ($stockoutOrderList as $key=>$item) {
            if($item['is_pickup_ordered'] == Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED) {
                continue;
            }
            $list[] = $item;
        }
        return $list;

    }

    /**
     * @param $arrStockoutOrderIds
     * @param $pickupOrderType
     * @return array
     */
    private function getCreateStockoutPickupOrderData($arrStockoutOrderIds, $pickupOrderType)
    {
        $list = [];
        switch ($pickupOrderType) {
            case  Order_Define_PickupOrder::PICKUP_ORDER_TYPE_NOT_SPLIT:
                $pickupOrderId = Order_Util_Util::generatePickupOrderId();
                foreach($arrStockoutOrderIds as $stockoutOrderId) {
                    $tmp['stockout_order_id'] = $stockoutOrderId;
                    $tmp['pickup_order_id'] = $pickupOrderId;
                    $list[] = $tmp;
                }
                break;
            case  Order_Define_PickupOrder::PICKUP_ORDER_TYPE_ORDER:
                foreach($arrStockoutOrderIds as $stockoutOrderId) {
                    $tmp['stockout_order_id'] = $stockoutOrderId;
                    $tmp['pickup_order_id'] = Order_Util_Util::generatePickupOrderId();
                    $list[] = $tmp;
                }
                break;
        }
        return $list;

    }

    private function getCreatePickupOrderData($arrStockoutPickOrderData, $stockoutOrderList,$pickupOrderType,$userId,$userName)
    {
        $list = [];
        foreach ($arrStockoutPickOrderData as $orderData)
        {
            $list[$orderData['pickup_order_id']][] = $orderData['stockout_order_id'];
        }

        $createParam = [];
        foreach ($list as $key=>$item) {
            $stockoutOrderId = $item[0];
            $tmp['pickup_order_id'] = $key;
            $tmp['warehouse_id'] = isset($stockoutOrderList[$stockoutOrderId]) ? $stockoutOrderList[$stockoutOrderId]['warehouse_id']:0;
            $tmp['warehouse_name'] = isset($stockoutOrderList[$stockoutOrderId]) ? $stockoutOrderList[$stockoutOrderId]['warehouse_name']:'';
            $tmp['pickup_order_status'] = Order_Define_PickupOrder::PICKUP_ORDER_STATUS_INIT;
            $tmp['pickup_order_type'] = $pickupOrderType;
            $result = $this->calculatePickupOrderStatisticsInfo($item, $stockoutOrderList);
            $tmp['stockout_order_amount'] = !empty($result['stockout_order_amount']) ? $result['stockout_order_amount']:0;
            $tmp['sku_distribute_amount'] = !empty($result['sku_distribute_amount']) ? $result['sku_distribute_amount']:0;
            $tmp['sku_pickup_amount'] = !empty($result['sku_pickup_amount']) ? $result['sku_pickup_amount']:0;
            $tmp['creator'] = $userName;
            $tmp['update_operator'] = $userName;
            $tmp['sku_kind_amount'] = !empty($result['sku_kind_amount']) ? $result['sku_kind_amount']:0;
            $createParam[] = $tmp;
        }
        return $createParam;


    }

    private function calculatePickupOrderStatisticsInfo($stockoutOrderIds, $stockoutOrderList)
    {
        $list = [
            'stockout_order_amount'=>0,
            'sku_distribute_amount'=>0,
            'sku_pickup_amount'=>0,
            'sku_kind_amount'=>0,
        ];
        $arrConditions = [
            'stockout_order_id' => ['in', $stockoutOrderIds],
        ];
        $arrColumns = ['stockout_order_id','sku_id'];
        $skuList = $this->objOrmSku->findRows($arrColumns, $arrConditions);
        $skuIds = array_column($skuList,'sku_id');
        $skuIds = array_unique($skuIds);
        $list['sku_kind_amount'] = count($skuIds);
        foreach($stockoutOrderIds as $stockoutOrderId) {
            $list['stockout_order_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_amount'];
            $list['sku_distribute_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_distribute_amount'];
            $list['sku_pickup_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_pickup_amount'];

        }
        return $list;

    }

    private function getCreatePickupOrderSkuData($arrStockoutPickOrderData, $stockoutOrderList, $pickupOrderType)
    {
    }

}

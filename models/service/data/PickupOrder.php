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
        $res = ['failStockoutOrderIds'=>[],'sucessNum'=>0];
        if (!array_key_exists($pickupOrderType,Order_Define_PickupOrder::PICKUP_ORDER_TYPE_MAP)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR,'参数异常');
        }
        $totalPickupOrderNum = count($arrStockoutOrderIds);
        $arrStockoutOrderIds = Order_Util_Util::batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds);
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
            $arrPickupOrderSkuData = $this->getCreatePickupOrderSkuData($arrStockoutPickOrderData,$stockoutOrderList);
            Model_Orm_PickupOrderSku::batchInsert($arrPickupOrderSkuData, false);
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
        $res['sucessNum'] = count($arrStockoutOrderIds);
        $res['totalStockoutOrdedrIdNum'] = $totalPickupOrderNum;
        return $res;
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

    /**
     * @param $arrStockoutPickOrderData
     * @param $stockoutOrderList
     * @param $pickupOrderType
     * @param $userId
     * @param $userName
     * @return array
     */
    private function getCreatePickupOrderData($arrStockoutPickOrderData, $stockoutOrderList, $pickupOrderType, $userId, $userName)
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

    private function getCreatePickupOrderSkuData($arrStockoutPickOrderData, $stockoutOrderList)
    {
        $list = [];
        foreach ($arrStockoutPickOrderData as $orderData) {
            $list[$orderData['pickup_order_id']][] = $orderData['stockout_order_id'];
        }

        $createParam = [];

        foreach ($list as $key => $item) {
            $arrConditions = [
                'stockout_order_id' => ['in', $item],
            ];
            $arrColumns = $this->objOrmSku->getAllColumns();
            $skuList = $this->objOrmSku->findRows($arrColumns, $arrConditions);
            foreach ($skuList as $skuKey => $skuInfo) {
                $skuId = $skuInfo['sku_id'];
                if (!isset($createParam[$key."_" .$skuId])) {
                    $createParam[$key."_" .$skuId]['sku_id'] = $skuId;
                    $createParam[$key . "_" . $skuId]['upc_id'] = $skuInfo['upc_id'];
                    $createParam[$key . "_" . $skuId]['sku_name'] = $skuInfo['sku_name'];
                    $createParam[$key . "_" . $skuId]['sku_net'] = $skuInfo['sku_net'];
                    $createParam[$key . "_" . $skuId]['sku_net_unit'] = $skuInfo['sku_net_unit'];
                    $createParam[$key . "_" . $skuId]['upc_unit'] = $skuInfo['upc_unit'];
                    $createParam[$key . "_" . $skuId]['upc_unit_num'] = $skuInfo['upc_unit_num'];
                    $createParam[$key . "_" . $skuId]['order_amount'] = $skuInfo['order_amount'];
                    $createParam[$key . "_" . $skuId]['distribute_amount'] = $skuInfo['distribute_amount'];
                    $createParam[$key . "_" . $skuId]['pickup_order_id'] = $key;
                    continue;
                }
                $createParam[$key . "_" . $skuId]['upc_unit_num'] += $skuInfo['upc_unit_num'];
                $createParam[$key . "_" . $skuId]['order_amount'] += $skuInfo['order_amount'];
                $createParam[$key . "_" . $skuId]['distribute_amount'] += $skuInfo['distribute_amount'];
            }


        }
        return $createParam;
    }

    /**
     * 根据pickup_order_id获取拣货单信息
     * @param $intPickupOrderId
     * @return mixed
     * @throws Order_BusinessError
     */
    public function getPickupOrderByPickupOrderId($intPickupOrderId)
    {
        $arrConds = [
            'pickup_order_id' => $intPickupOrderId,
            'is_delete'       => Order_Define_Const::NOT_DELETE,
        ];
        $arrPickupOrder = Model_Orm_PickupOrder::findRow(Model_Orm_PickupOrder::getAllColumns(), $arrConds);
        if (empty($arrPickupOrder)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }

        $arrPickupOrderSkus = Model_Orm_PickupOrderSku::findRows(Model_Orm_PickupOrderSku::getAllColumns(), $arrConds);
        $arrPickupOrder['pickup_skus'] = $arrPickupOrderSkus;
        return $arrPickupOrder;
    }

    /**
     * get pick up order list
     * @param $strWarehouseIds
     * @param $intCreateStartTime
     * @param $intCreateEndTime
     * @param $intPageSize
     * @param int $intPageNum
     * @param int $intStockoutOrderId
     * @param int $intPickupOrderId
     * @param int $intPickupOrderIsPrint
     * @param int $intUpdateStartTime
     * @param int $intUpdateEndTime
     * @return array
     * @throws Order_BusinessError
     */
    public function getPickupOrderList($strWarehouseIds,
                                       $intCreateStartTime,
                                       $intCreateEndTime,
                                       $intPageSize,
                                       $intPageNum = 1,
                                       $intStockoutOrderId = 0,
                                       $intPickupOrderId = 0,
                                       $intPickupOrderIsPrint = 0,
                                       $intUpdateStartTime = 0,
                                       $intUpdateEndTime = 0)
    {
        $ret = [];
        // check time range
        if (false === Order_Util::verifyUnixTimeSpan(
                $intCreateStartTime,
                $intCreateEndTime)) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }
        if (false === Order_Util::verifyUnixTimeSpan(
                $intUpdateStartTime,
                $intUpdateEndTime)) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }
        // check page
        if (empty($intPageSize)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        // check warehouses
        if(empty($strWarehouseIds)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrWarehouseIds = Order_Util::extractIntArray($strWarehouseIds);
        $ret = Model_Orm_PickupOrder::getPickupOrderList($arrWarehouseIds,
            $intCreateStartTime,
            $intCreateEndTime,
            $intPageSize,
            $intPageNum,
            $intStockoutOrderId,
            $intPickupOrderId,
            $intPickupOrderIsPrint,
            $intUpdateStartTime,
            $intUpdateEndTime);
        return $ret;
    }
}

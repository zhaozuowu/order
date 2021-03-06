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
        $this->objWrpcTms = new Dao_Wrpc_Tms();
        $this->objWrpcStock = new Dao_Wrpc_Stock(Order_Define_Wrpc::NWMS_STOCK_SERVICE_NAME);
        $this->objDaoWrpcWarehouse = new Dao_Wrpc_Warehouse();
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
        $res = ['failStockoutOrderIds'=>[],'sucessNum'=>0,'pickupOrders'=>''];
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
        $arrStockoutPickOrderData = $this->getCreateStockoutPickupOrderData($arrStockoutOrderIds,$pickupOrderType);
        $res['pickupOrders'] = array_column($arrStockoutPickOrderData,'pickup_order_id');
        $res['pickupOrders'] = array_unique($res['pickupOrders']);
        $res['pickupOrders'] = implode(",",$res['pickupOrders']);
        Model_Orm_PickupOrder::getConnection()->transaction(function () use ($stockoutOrderList,$arrStockoutOrderIds,$pickupOrderType,$userId,$userName,$arrStockoutOrderIds,$arrStockoutPickOrderData) {
            Model_Orm_StockoutPickupOrder::batchInsert($arrStockoutPickOrderData, false);
            $arrPickupOrderData  = $this->getCreatePickupOrderData($arrStockoutPickOrderData,$stockoutOrderList,$pickupOrderType,$userId,$userName);
            Model_Orm_PickupOrder::batchInsert($arrPickupOrderData, false);
            $wareHouseIds = array_column($arrPickupOrderData,'warehouse_id','pickup_order_id');
            $arrPickupOrderSkuData = $this->getCreatePickupOrderSkuData($arrStockoutPickOrderData,$stockoutOrderList,$wareHouseIds);
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
            $tmp['stockout_order_amount'] = count($item);
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
            //$list['stockout_order_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_amount'];
            $list['sku_distribute_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_distribute_amount'];
            $list['sku_pickup_amount']+= $stockoutOrderList[$stockoutOrderId]['stockout_order_pickup_amount'];

        }
        return $list;

    }

    /**
     * 拼接拣货单sku信息
     * @param $arrStockoutPickOrderData
     * @param $stockoutOrderList
     * @param $wareHouseIds
     * @return array
     */
    private function getCreatePickupOrderSkuData($arrStockoutPickOrderData, $stockoutOrderList, $wareHouseIds)
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
            $details  = [];
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
                    $createParam[$key . "_" . $skuId]['pickup_extra_info'] = '';
                    $details[$key."_" .$skuId]['sku_id'] = $skuId;
                    $details[$key."_" .$skuId]['amount']= $skuInfo['distribute_amount'];
                    continue;
                }
                $createParam[$key . "_" . $skuId]['upc_unit_num'] += $skuInfo['upc_unit_num'];
                $createParam[$key . "_" . $skuId]['order_amount'] += $skuInfo['order_amount'];
                $createParam[$key . "_" . $skuId]['distribute_amount'] += $skuInfo['distribute_amount'];
                $details[$key."_" .$skuId]['amount']+= $skuInfo['distribute_amount'];
            }
            $pickupOrderId = $key;
           $intWarehouseId = isset($wareHouseIds[$key]) ? $wareHouseIds[$key]:0;
            //$arrWarehouseInfo = $this->objDaoWrpcWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
          //  $intLocationTag = $arrWarehouseInfo['storage_location_tag'];
            if (!empty($details)) {
                $recommendStockLocList = $this->objWrpcStock->getRecommendStockLoc($intWarehouseId,$pickupOrderId,$details);
                $recommendStockLocList = $this->formatRecommendStockLocList($recommendStockLocList);
                foreach($recommendStockLocList as $stockKey=>$stockItem) {
                    if (isset($createParam[$key."_" .$stockKey])) {
                        $createInfo['create_info'] = $stockItem;
                        $createParam[$key."_" .$stockKey]['pickup_extra_info'] = json_encode($createInfo);
                    }
                }
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
        $stockoutOrderIds =   Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($arrPickupOrder['pickup_order_id']);
        $arrPickupOrder['stockout_order_ids'] = $stockoutOrderIds;
        $arrPickupOrderSkus = Model_Orm_PickupOrderSku::findRows(Model_Orm_PickupOrderSku::getAllColumns(), $arrConds);
        $skuIds = array_column($arrPickupOrderSkus,'sku_id');
        $arrSkusInfo = [];
        if(!empty($skuIds)){
            //获取sku基础信息判断产效期类型
            $objRalSku = new Dao_Ral_Sku();
            $arrSkusInfo = $objRalSku->getSkuInfos($skuIds);
            $arrSkusInfo = array_column($arrSkusInfo,'sku_effect_type','sku_id');
        }
        $arrPickupOrder['pickup_skus'] = $arrPickupOrderSkus;
        $arrPickupOrder['pickup_sku_effect_type_list'] = $arrSkusInfo;
        return $arrPickupOrder;
    }

    /**
     * 捡货单打印批量(批量)
     * @param $pickupOrderIds
     * @return array
     * @throws Order_BusinessError
     */
    public  function getPickupOrderPrintByPickupOrderIds($pickupOrderIds)
    {
        $arrPickupOrderIds = explode(',',$pickupOrderIds);
        $arrConds = [
            'pickup_order_id' => ['in', $arrPickupOrderIds],
            'is_delete'       => Order_Define_Const::NOT_DELETE,
        ];
        $arrPickupOrder = Model_Orm_PickupOrder::findRows(Model_Orm_PickupOrder::getAllColumns(), $arrConds);
        if (empty($arrPickupOrder)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }
        $list = [];
        foreach ($arrPickupOrder as $key=>$pickupOrderItem){
            $type = $pickupOrderItem['pickup_order_type'];
            if($type == Order_Define_PickupOrder::PICKUP_ORDER_TYPE_NOT_SPLIT) {
                $list[] = $this->getCollectPickupOrderPrintList($pickupOrderItem);
            }elseif($type == Order_Define_PickupOrder::PICKUP_ORDER_TYPE_ORDER){
                $list[] =  $this->getPickupOrderPrintList($pickupOrderItem);
            }
            if($pickupOrderItem['pickup_order_is_print'] ==Order_Define_PickupOrder::PICKUP_ORDER_NOT_PRINTED) {
                $updateData['pickup_order_is_print'] = Order_Define_PickupOrder::PICKUP_ORDER_PRINTED;
                $res = Model_Orm_PickupOrder::findOne(['pickup_order_id' =>$pickupOrderItem['pickup_order_id']])->update($updateData);
            }
        }
        return $list;

    }

    /**
     * @param $arrPickupOrderIds
     * @return array
     */
    public function getPickupOrderSkuInfoByPickupOrderIds($arrPickupOrderIds)
    {
        $arrPickupOrderIds = array_map(function ($intPickupOrderId) {
            return intval($intPickupOrderId);
        }, $arrPickupOrderIds);
        $arrPickupOrderIds = array_unique($arrPickupOrderIds);
        $arrPickupOrderSkuInfo = Model_Orm_PickupOrderSku::getOrderSkuInfoByOrderIds($arrPickupOrderIds);
        return $arrPickupOrderSkuInfo;
    }

    /**
     * get pick up order list
     * @param $strWarehouseIds
     * @param $intCreateStartTime
     * @param $intCreateEndTime
     * @param $intPageSize
     * @param int $intPageNum
     * @param int $intPickupOrderStatus
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
                                       $intPickupOrderStatus = 0,
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
        // check pickup order status
        $strPickupOrderStatus = strval($intPickupOrderStatus);
        if (!empty($intPickupOrderStatus) &&
            isset(Order_Define_PickupOrder::PICKUP_ORDER_STATUS_MAP[$strPickupOrderStatus])) {
            $intPickupOrderStatus = intval($intPickupOrderStatus);
        } else {
            $intPickupOrderStatus = 0;
        }
        // check warehouses
        if(empty($strWarehouseIds)){
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        // query pickup order ids by stockout order id
        if (!empty($intStockoutOrderId)) {
            $arrPickupOrderIds = Model_Orm_StockoutPickupOrder::getPickupOrderIdsByStockoutOrderId($intStockoutOrderId);
            if (empty($arrPickupOrderIds)) {
                return $ret;
            }
        } else {
            $arrPickupOrderIds = [];
        }
        // and logical
        if (!empty($intPickupOrderId)) {
            $arrPickupOrderIds = [
                $intPickupOrderId,
            ];
        }
        $arrWarehouseIds = Order_Util::extractIntArray($strWarehouseIds);
        $ret = Model_Orm_PickupOrder::getPickupOrderList($arrWarehouseIds,
            $intCreateStartTime,
            $intCreateEndTime,
            $intPageSize,
            $intPageNum,
            $intPickupOrderStatus,
            $arrPickupOrderIds,
            $intPickupOrderIsPrint,
            $intUpdateStartTime,
            $intUpdateEndTime);
        if(!empty($ret['total'])) {
            $pickupOrderIds = array_column($ret['rows'],'pickup_order_id');
            $stockoutPickupOrderList =  Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderIds($pickupOrderIds);
            $list = [];
            foreach($stockoutPickupOrderList as $key=>$item) {
                 $list[$item['pickup_order_id']][] = $item['stockout_order_id'];
            }
        }
        $ret['relations'] = $list;
        return $ret;
    }

    /**
     * @param $arrWarehouseIds
     * @return int
     */
    public function getCountByWaiting($arrWarehouseIds)
    {
        $arrInputWarehouseIds = array_map(function ($intWarehouseId) {
            return intval($intWarehouseId);
        }, $arrWarehouseIds);
        $arrInputWarehouseIds = array_unique($arrInputWarehouseIds);
        Bd_Log::debug('input warehouse id: ' . json_encode($arrInputWarehouseIds));

        $arrCond = [
            'warehouse_id' => ['in', $arrInputWarehouseIds],
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        $list = Model_Orm_PickupOrder::find($arrCond)->groupby(['pickup_order_status'])->select(['pickup_order_status','count(pickup_order_id) as pickupOrderNum'])->rows();
        return $list;
    }

    /**
     * 打印
     * @param $pickupOrderId
     * @return array
     */
    public function getPickupRowsPrintList($pickupOrderId)
    {
        $list = [];
        $arrStockoutOrderIds = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($pickupOrderId);
        if (empty($arrStockoutOrderIds)) {
            return $list;
        }

        $arrConditions = [
            'stockout_order_id' => ['in', $arrStockoutOrderIds],
        ];
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $stockoutOrderList= $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        if (empty($stockoutOrderList)) {
            return [];
        }
        $arrColumns = $this->objOrmSku->getAllColumns();
        $stockoutOrderSkuList= $this->objOrmSku->findRows($arrColumns, $arrConditions);
        $skuList = [];
        foreach($stockoutOrderSkuList as $skuKey=>$skuItem) {
            $skuList[$skuItem['stockout_order_id']][] = $skuItem;
        }
        foreach ($stockoutOrderList as $key =>$item) {
            $item['stockout_order_skuinfo'] = isset($skuList[$item['stockout_order_id']]) ? $skuList[$item['stockout_order_id']]:[];
            $list[$item['pickup_tms_snapshoot_num']][] = $item;
        }
        return $list;
    }

    /**
     * 取消拣货单
     * @param $intPickupOrderId
     * @param $userId
     * @param $userName
     * @return int
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function cancelPickupOrderById($intPickupOrderId,  $userId, $userName)
    {
        if (empty($intPickupOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $objPickupOrder = Model_Orm_PickupOrder::getPickupOrderInfo($intPickupOrderId);

        if (empty($objPickupOrder)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }
        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_CANCEL == $objPickupOrder->pickup_order_status) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_IS_CANCELED);
        }
        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED == $objPickupOrder->pickup_order_status) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_IS_FINISHED);
        }
        //update obj
        $objPickupOrder->pickup_order_status = Order_Define_PickupOrder::PICKUP_ORDER_STATUS_CANCEL;
        $objPickupOrder->update_operator = $userName;
        $intWarehouseId = $objPickupOrder->warehouse_id;
        Model_Orm_PickupOrder::getConnection()->transaction(function () use ($objPickupOrder, $intPickupOrderId, $intWarehouseId) {
            $updateFlag =  $objPickupOrder->update();
            if (!$updateFlag) {
                Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_CANCEL_FAILED);
            }
            //修改出库单为未生成拣货任务
            $arrStockOutOrderIds = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($intPickupOrderId);
            foreach ($arrStockOutOrderIds as $intStockOutOrderId) {
                $objStockOutOrderInfo = Model_Orm_StockoutOrder::getStockoutOrderObjByOrderId($intStockOutOrderId);
                if (!empty($objStockOutOrderInfo)) {
                    $objStockOutOrderInfo->is_pickup_ordered = Order_Define_StockoutOrder::PICKUP_ORDERE_NOT_CREATED;
                    $objStockOutOrderInfo->update();
                }
            }
            //作废
            $objDaoWrpcStock = new Dao_Wrpc_Stock();
            $objDaoWrpcStock->cancelStockLocRecommend($intPickupOrderId, $intWarehouseId);
        });
        return Order_Define_Const::UPDATE_SUCCESS;
    }

    /**
     * 拣货完成
     * @param int   $intPickupOrderId 拣货单id
     * @param array $arrPickupSkus  拣货单中sku
     * @param int   $userId 操作人id
     * @param string $userName 操作人name
     * @param string $strRemark
     * @return int
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function finishPickupOrder($intPickupOrderId, $arrPickupSkus, $userId, $userName, $strRemark)
    {
        if (empty($intPickupOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($userId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($userName)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($arrPickupSkus)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $objPickupOrderInfo = Model_Orm_PickupOrder::getPickupOrderInfo($intPickupOrderId);
        if (empty($objPickupOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }
        $intWarehouseId = $objPickupOrderInfo->warehouse_id;
        $arrPickupSkus = $this->checkAndAssemblePickupSkusDetailInfo($arrPickupSkus, $intWarehouseId);
        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED == $objPickupOrderInfo->pickup_order_status) {
            Bd_Log::warning("pickupOrder can't modify pickup_order_status by pickupOrderId:". $intPickupOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_IS_FINISHED);
        }
        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_CANCEL == $objPickupOrderInfo->pickup_order_status) {
            Bd_Log::warning("pickupOrder can't modify pickup_order_status by pickupOrderId:". $intPickupOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_IS_CANCELED);
        }
        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_INIT != $objPickupOrderInfo->pickup_order_status) {
            Bd_Log::warning("pickupOrder can't modify pickup_order_status by pickupOrderId:". $intPickupOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_STATUS_INVALID);
        }
        $boolCheckStatus = $this->checkoutPickupAmount($arrPickupSkus);
        if ($boolCheckStatus) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_AMOUNT_ERROR);
        }
        $arrPickupSkuPickupAmount = array_column($arrPickupSkus, 'pickup_amount');
        $intPickupOrderSkuAmount = array_sum($arrPickupSkuPickupAmount);
        $intPickupOrderSkuKindCount = 0;
        foreach ($arrPickupSkuPickupAmount as $intPickupAmount) {
            if (!empty($intPickupAmount)) {
                $intPickupOrderSkuKindCount ++;
            }
        }
        if ($intPickupOrderSkuAmount > $objPickupOrderInfo->sku_distribute_amount) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_AMOUNT_OVER_DISTRIBUTE);
        }
        //拼装更新sku数据
        list($arrSkuUpdateFields, $arrSkuUpdateCondition) = $this->assemblePickupOrderSkuList($arrPickupSkus, $intPickupOrderId);
        //开启事务写入数据
        Model_Orm_PickupOrder::getConnection()->transaction(function () use ($arrSkuUpdateFields, $arrSkuUpdateCondition,
                $intPickupOrderId, $userName, $userId, $intPickupOrderSkuAmount, $intPickupOrderSkuKindCount,
                $intWarehouseId, $arrPickupSkus, $strRemark){
            $arrOrderUpdateFields = [
                'sku_pickup_amount' => $intPickupOrderSkuAmount,
                'sku_kind_amount' => $intPickupOrderSkuKindCount,
                'update_operator' => $userName,
                'remark' => $strRemark,
                'pickup_order_status' => Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED,
            ];
            //更新订单数据
            Model_Orm_PickupOrder::updatePickupOrderInfoById($intPickupOrderId, $arrOrderUpdateFields);
            //更新拣货单sku
            Model_Orm_PickupOrderSku::updatePickupInfo($arrSkuUpdateFields, $arrSkuUpdateCondition);
            //通知stock拣货完成
            $objDaoWrpcStock = new Dao_Wrpc_Stock();
            $objDaoWrpcStock->pickStock($intPickupOrderId, $intWarehouseId, $arrPickupSkus);
            //异步更新出库单
            $strCmd = Order_Define_Cmd::CMD_FINISH_STOCKOUT_ORDER;
            $arrParams = [
                'pickup_order_id' => $intPickupOrderId,
                'pickup_skus' => $arrPickupSkus,
                'user_id' => $userId,
                'user_name' => $userName,
            ];
            $strKey = strval($intPickupOrderId);
            Order_Wmq_Commit::sendWmqCmd($strCmd, $arrParams, $strKey);
        });
        return Order_Define_Const::UPDATE_SUCCESS;
    }

    /**
     * 拣货数量检查
     * @param array $arrPickupSkus
     * @return bool
     */
    private function checkoutPickupAmount($arrPickupSkus): bool
    {
        $boolCheckStatus = true;
        foreach ($arrPickupSkus as $arrSkuInfo) {
            if (!empty($arrSkuInfo['pickup_amount'])) {
                $boolCheckStatus = false;
                break;
            }
        }
        return $boolCheckStatus;
    }

    /**
     * 拼装拣货商品DB数据
     * @param array $arrPickupSkus
     * @param int   $intPickupOrderId
     * @return array
     */
    private function assemblePickupOrderSkuList($arrPickupSkus, $intPickupOrderId)
    {

        $arrUpdateCondition = [];
        $arrUpdateFields = [];
        foreach ($arrPickupSkus as $arrSkuInfo) {
            $arrUpdateFields[] = [
                'pickup_amount' => $arrSkuInfo['pickup_amount'],
                'pickup_extra_info' => json_encode($arrSkuInfo['pick_infos']),
            ];
            $arrUpdateCondition[] = [
                'pickup_order_id' => $intPickupOrderId,
                'sku_id' => $arrSkuInfo['sku_id'],
            ];
        }
        return [
            $arrUpdateFields,
            $arrUpdateCondition,
        ];
    }

    /**
     * 获取sku库区库位
     * @param $intPickupOrderId
     * @param $intSkuId
     * @param $strLocationCode
     * @param $intExpireTime
     * @return array
     * @throws Order_BusinessError
     * @throws Nscm_Exception_Error
     */
    public function getSkuLocation($intPickupOrderId, $intSkuId, $strLocationCode, $intExpireTime)
    {
        if (empty($intPickupOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        if (empty($intSkuId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $arrPickupOrderInfo = Model_Orm_PickupOrder::getPickupOrderInfo($intPickupOrderId, true);
        if (empty($arrPickupOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }
        //获取sku基础信息判断产效期类型
        $objRalSku = new Dao_Ral_Sku();
        $arrSkusInfo = $objRalSku->getSkuInfos([$intSkuId]);
        $intSkuEffectType = $arrSkusInfo[$intSkuId]['sku_effect_type'];
        $intWarehouseId = $arrPickupOrderInfo['warehouse_id'];
        $strTimeParam = 'production_time';
        if (Order_Define_Sku::SKU_EFFECT_TYPE_EXPIRE == $intSkuEffectType) {
            $strTimeParam = 'expiration_time';
        }

        $objWrpc = new Dao_Wrpc_Stock(Order_Define_Wrpc::STOCK_INFO_SERVICE);
        $arrSkusLocationList =  $objWrpc->getSkuLocation($intWarehouseId, $intSkuId, $strLocationCode, $strTimeParam, $intExpireTime);
        $arrSkusLocationListRet = [];
        foreach ($arrSkusLocationList as $arrSkuLocation) {
            $arrSkuLocationListItem = [
                'sku_id' => $arrSkuLocation['sku_id'],
                'location_code' => $arrSkuLocation['location_code'],
                'expiration_time' => $arrSkuLocation['expiration_time'],
                'pickable_amount' => $arrSkuLocation['pickable_amount'],
            ];
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $intSkuEffectType) {
                $arrSkuLocationListItem['time'] = strtotime(date('Y-m-d',
                    $arrSkuLocation['production_time']));
                $arrSkuLocationListItem['expire_time'] = $arrSkuLocation['production_time'];
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $arrSkuLocationListItem['time'] = strtotime(date('Y-m-d',
                    $arrSkuLocation['expiration_time']));
                $arrSkuLocationListItem['expire_time'] = $arrSkuLocation['expiration_time'];
            }
            $arrSkusLocationListRet[] = $arrSkuLocationListItem;
        }

        return $arrSkusLocationListRet;
    }

    /**
     * @param $intPickupOrderId
     * @throws Order_BusinessError
     */
    public function fixPickupOrder($intPickupOrderId)
    {
        $objPickupOrderInfo = Model_Orm_PickupOrder::getPickupOrderInfo($intPickupOrderId);
        if (empty($objPickupOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_NOT_EXISTED);
        }

        if (Order_Define_PickupOrder::PICKUP_ORDER_STATUS_CANCEL != $objPickupOrderInfo->pickup_order_status) {
            Bd_Log::warning("pickupOrder can't modify pickup_order_status by pickupOrderId:". $intPickupOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_IS_FINISHED);
        }
        //更新订单数据
        Model_Orm_PickupOrder::getConnection()->transaction(function () use ($intPickupOrderId){
            $arrOrderUpdateFields = [
                'pickup_order_status' => Order_Define_PickupOrder::PICKUP_ORDER_STATUS_INIT,
            ];
            //更新订单数据
            Model_Orm_PickupOrder::updatePickupOrderInfoById($intPickupOrderId, $arrOrderUpdateFields);

            $arrStockoutOrderIds = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($intPickupOrderId);
            if (empty($arrStockoutOrderIds)) {
                return [];
            }
            $updateData = [
                'is_pickup_ordered' => Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED,
            ];
            $arrConditions = [
                'stockout_order_id' => ['in', $arrStockoutOrderIds],
            ];
            $result = $this->objOrmStockoutOrder->updateDataByConditions($arrConditions, $updateData);
            return ['successNum'=>$result];
        });


    }

    /**
     * 通知tms完成拣货（wmq）
     * @param $strStockoutOrderId
     * @param $pickupSkus
     * @return array
     * @throws Order_BusinessError
     */
    private function notifyTmsFnishPick($strStockoutOrderId, $pickupSkus)
    {
        $intShipmentOrderId = Model_Orm_StockoutOrder::
        getShipmentOrderIdByStockoutOrderId($strStockoutOrderId);
        $arrStockoutParams = [
            'stockout_order_id' => strval($strStockoutOrderId),
            'shipment_order_id' => strval($intShipmentOrderId),
            'pickup_skus' => $pickupSkus
        ];
        $strCmd = Order_Define_Cmd::CMD_FINISH_PRICKUP_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, strval($strStockoutOrderId));
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL);
        }
        return [];
    }

    /**
     * 获取下一步操作的出库单操作状态
     * @param $stockoutOrderStatus
     * @return bool
     */
    private function getNextStockoutOrderStatus($stockoutOrderStatus)
    {
        $stockoutOrderList = Order_Define_StockoutOrder::STOCK_OUT_ORDER_STATUS_LIST;
        if (!array_key_exists($stockoutOrderStatus, $stockoutOrderList)) {
            return false;
        }
        $keys = array_keys($stockoutOrderList);
        $result = $keys[array_search($stockoutOrderStatus, $keys) + 1] ?? false;
        return $result;
    }

    /**
     * write log
     * @param $operatorId
     * @param $userName
     * @param $operationType
     * @param $quotaIdxInt1
     * @param $content
     */
    private function addLog($operatorId, $userName, $content, $operationType, $quotaIdxInt1)
    {
        $logType = Order_Define_StockoutOrder::APP_NWMS_ORDER_LOG_TYPE;
        (new Dao_Ral_Log())->addLog($logType,$quotaIdxInt1,$operationType,$userName,$operatorId,$content);
    }

    /**
     * 获取tms排线号
     * @param $pickupOrderId
     * @return array
     */
    public function getTmsSnapshootNum($pickupOrderId)
    {

        $res = ['orderNum'=>0,'tmsSnapshootNum'=>0,'hasSnapshootOrderNum'=>0,'noSnapshootOrderNum'=>0];
        $arrStockoutOrderIds = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($pickupOrderId);
        if (empty($arrStockoutOrderIds)) {
            return $res;
        }
        $arrConditions = [
            'stockout_order_id' => ['in', $arrStockoutOrderIds],
        ];
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $stockoutOrderList= $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        if (empty($stockoutOrderList)) {
            return $res;
        }
        $arrShipmentOrderIds = array_column($stockoutOrderList,'shipment_order_id');
        $params = ['shipmentIds'=>$arrShipmentOrderIds];
        $tmsSnapshootList = $this->objWrpcTms->getTmsSnapshootNum($params);
        $res['orderNum'] = count($arrStockoutOrderIds);
        if(empty($tmsSnapshootList)) {
          $res['noSnapshootOrderNum'] = $res['orderNum'];
          return $res;
        }
        $res['tmsSnapshootNum'] = count(array_unique($tmsSnapshootList));
        $res['hasSnapshootOrderNum'] = 0;
        $res['noSnapshootOrderNum'] = 0;
        foreach($stockoutOrderList as $key=>$item) {
            if(isset($tmsSnapshootList[$item['shipment_order_id']])) {
                $updateData['pickup_tms_snapshoot_num'] = $tmsSnapshootList[$item['shipment_order_id']];
                $res['hasSnapshootOrderNum']++;
                $arrConditions = [
                    'stockout_order_id' => $item['stockout_order_id']
                ];
                $result = $this->objOrmStockoutOrder->updateDataByConditions($arrConditions, $updateData);
                continue;
            }
            $res['noSnapshootOrderNum']++;
        }
        return $res;
    }

    /**
     * 格式化库位推荐列表
     * @param $recommendStockLocList
     * @return array
     */
    private function formatRecommendStockLocList($recommendStockLocList)
    {
        
        $list = [];
        foreach ($recommendStockLocList as $key=>$item) {
            $tmp['location_code'] = $item['location_code'];
            $tmp['recommend_amount'] = $item['recommend_amount'];
            $tmp['expiration_time'] = empty($item['expiration_time']) ? 0: $item['expiration_time'];
            $tmp['production_time'] = empty($item['production_time']) ? 0:$item['production_time'];
            $list[$item['sku_id']][] = $tmp;
        }
        return $list;
    }

    /**
     * 订单拣货单打印
     * @param $pickupOrder
     * @return array
     */
    private function getPickupOrderPrintList($pickupOrder)
    {
        if (empty($pickupOrder)) {
            return [];
        }

        $arrConds = [
            'pickup_order_id' => $pickupOrder['pickup_order_id'],
            'is_delete'       => Order_Define_Const::NOT_DELETE,
        ];
        $list = [
            'pickup_order_type'=>$pickupOrder['pickup_order_type'],
            'pickup_order_id'=>$pickupOrder['pickup_order_id'],
            'stockout_order_id'=>0,
            'logistics_order_id'=>0,
            'warehouse_name'=>'',
            'customer_name'=>'',
            'customer_id'=>'',
            'customer_contactor'=>'',
            'customer_contact'=>'',
            'customer_address'=>'',
            'order_supply_type'=>'',
            'devices'=>'',
            'remark'=> '',
        ];
        $stockoutPickup = Model_Orm_StockoutPickupOrder::findRow(Model_Orm_StockoutPickupOrder::getAllColumns(),$arrConds);
        if(!empty($stockoutPickup))
        {
            $arrConds = [
                'stockout_order_id' => $stockoutPickup['stockout_order_id'],
                'is_delete'       => Order_Define_Const::NOT_DELETE,
            ];

            $stockoutOrderList = Model_Orm_StockoutOrder::findRow(Model_Orm_StockoutOrder::getAllColumns(),$arrConds);
            if(!empty($stockoutOrderList)){
                $list['stockout_order_id'] = $stockoutOrderList['stockout_order_id'];
                $list['logistics_order_id'] = $stockoutOrderList['logistics_order_id'];
                $list['warehouse_name'] = $stockoutOrderList['warehouse_name'];
                $list['customer_name'] = $stockoutOrderList['customer_name'];
                $list['customer_id'] = $stockoutOrderList['customer_id'];
                $list['customer_contactor'] = $stockoutOrderList['customer_contactor'];
                $list['customer_contact'] = $stockoutOrderList['customer_contact'];
                $list['customer_address'] = $stockoutOrderList['customer_address'];
                $arrShelfInfo = json_decode($stockoutOrderList['shelf_info'], true);
                $list['order_supply_type'] = isset(Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrShelfInfo['supply_type']])?Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrShelfInfo['supply_type']]:'';
                $list['devices']  = Order_Define_Format::formatDevices($arrShelfInfo['devices']);
                $list['remark']  = $stockoutOrderList['stockout_order_remark'];

            }

        }
        $skus = $this->getPickupOrderSkuPrintList($pickupOrder['pickup_order_id'],$pickupOrder['pickup_order_status']);
        $list['pickup_skus'] = $skus;
        $arrWarehouseInfo = $this->objDaoWrpcWarehouse->getWarehouseInfoByWarehouseId($pickupOrder['warehouse_id']);
        $intLocationTag = $arrWarehouseInfo['storage_location_tag'];
        $list['is_recommend'] = ($intLocationTag == Order_Define_Warehouse::STORAGE_LOCATION_TAG_ENABLED)?1:0;
        return $list;
    }

    /**
     * 汇总拣货单打印
     * @param $pickupOrder
     * @return array
     */
    private function getCollectPickupOrderPrintList($pickupOrder)
    {
        if (empty($pickupOrder)) {
            return [];
        }
        $arrWarehouseInfo = $this->objDaoWrpcWarehouse->getWarehouseInfoByWarehouseId($pickupOrder['warehouse_id']);
        $intLocationTag = $arrWarehouseInfo['storage_location_tag'];
        $arrConds = [
            'pickup_order_id' => $pickupOrder['pickup_order_id'],
            'is_delete'       => Order_Define_Const::NOT_DELETE,
        ];
        $list = [
          'stockout_order_amount' => empty($pickupOrder['stockout_order_amount']) ? 0:$pickupOrder['stockout_order_amount'],
          'sku_pickup_amount' => empty($pickupOrder['sku_pickup_amount']) ? 0:$pickupOrder['sku_pickup_amount'],
          'pickup_order_type'=>$pickupOrder['pickup_order_type'],
          'pickup_order_id'=>$pickupOrder['pickup_order_id'],
        ];
        if(Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED !=$pickupOrder['pickup_order_status'] ) {
            $list['sku_pickup_amount'] = empty($pickupOrder['sku_distribute_amount']) ? 0:$pickupOrder['sku_distribute_amount'];
        }
        $list['is_recommend'] = ($intLocationTag == Order_Define_Warehouse::STORAGE_LOCATION_TAG_ENABLED)?1:0;

        $skus = $this->getPickupOrderSkuPrintList($pickupOrder['pickup_order_id'],$pickupOrder['pickup_order_status']);
        $list['pickup_skus'] = $skus;
        return $list;


    }

    /**
     * 格式化实际库位信息
     * @param $pickupExtraInfo
     * @param $arrSkusInfo
     * @param $intSkuId
     * @return array
     */
    private function formatRealPickupExtraInfo($pickupExtraInfo)
    {
        $list = [];
        $pickupExtraInfo =  json_decode($pickupExtraInfo, true);
        $recommendList =  empty($pickupExtraInfo['finish_info']) ? []:$pickupExtraInfo['finish_info'];
        $recommendList = empty($recommendList) ? []:json_decode($recommendList,true);
        if(empty($recommendList)) {
            $tmp['expire_time'] = 0;
            $tmp['recommend_amount'] = 0;
            $tmp['location_code'] = '';
            $list[] = $tmp;
            return $list;
        }
        foreach ($recommendList as $key=>$item) {
            $tmp['expire_time'] = $item['expiration_time'];
            $tmp['recommend_amount'] = $item['pick_amount'];
            $tmp['location_code'] = $item['location_code'];
            $list[] = $tmp;
        }
        return $list;
    }
    /**
     * 格式化推荐库位信息
     * @param $pickupExtraInfo
     * @param $arrSkusInfo
     * @param $intSkuId
     * @return array
     */
    private function formatRecommendPickupExtraInfo($pickupExtraInfo, $arrSkusInfo, $intSkuId)
    {
        $list = [];
        $intSkuEffectType = isset($arrSkusInfo[$intSkuId]) ? $arrSkusInfo[$intSkuId]:0;
        $pickupExtraInfo =  json_decode($pickupExtraInfo, true);
        $recommendList =  empty($pickupExtraInfo['create_info']) ? []:$pickupExtraInfo['create_info'];
        foreach ($recommendList as $key=>$item) {
            $tmp['expire_time'] = 0;
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $intSkuEffectType) {

                $tmp['expire_time'] = $item['production_time'];
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $tmp['expire_time'] = $item['expiration_time'];
            }
            $tmp['recommend_amount'] = $item['recommend_amount'];
            $tmp['location_code'] = $item['location_code'];
            $list[] = $tmp;
        }
        return $list;
    }

    /**
     * 获取拣货单商品打印信息
     * @param $pickupOrderId
     * @return array
     * @throws Nscm_Exception_Error
     */
    private function getPickupOrderSkuPrintList($pickupOrderId,$pickupOrderStatus)
    {
        $arrConds = [
            'pickup_order_id' =>$pickupOrderId,
            'is_delete'       => Order_Define_Const::NOT_DELETE,
        ];
        $arrPickupOrderSkus = Model_Orm_PickupOrderSku::findRows(Model_Orm_PickupOrderSku::getAllColumns(), $arrConds);
        $skuIds = array_column($arrPickupOrderSkus,'sku_id');
        $arrSkusInfo = [];
        if(!empty($skuIds)){
            //获取sku基础信息判断产效期类型
            $objRalSku = new Dao_Ral_Sku();
            $arrSkusInfo = $objRalSku->getSkuInfos($skuIds);
            $arrSkusInfo = array_column($arrSkusInfo,'sku_effect_type','sku_id');
        }
        $list = [];
        foreach($arrPickupOrderSkus as $skuInfo) {
            $tmp['upc_id'] = $skuInfo['upc_id'];
            $tmp['sku_name'] = $skuInfo['sku_name'];
            $tmp['sku_net'] = $skuInfo['sku_net'].Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$skuInfo['sku_net_unit']];
            $tmp['sku_net_text'] = $skuInfo['sku_net'].Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$skuInfo['sku_net_unit']];
            $tmp['upc_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$skuInfo['upc_unit']];
            $tmp['distribute_amount'] =$skuInfo['distribute_amount'];
            $tmp['pickup_amount'] =$skuInfo['pickup_amount'];
            $tmp['differences_amount'] = 0;
            if($pickupOrderStatus == Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED )
            {
                $tmp['recommend_pickup_extra_info'] = $this->formatRealPickupExtraInfo($skuInfo['pickup_extra_info'],$arrSkusInfo,$skuInfo['sku_id']);
            }else{
                $tmp['recommend_pickup_extra_info'] = $this->formatRecommendPickupExtraInfo($skuInfo['pickup_extra_info'],$arrSkusInfo,$skuInfo['sku_id']);
                $arrRecommendAmount = array_column( $tmp['recommend_pickup_extra_info'],'recommend_amount');
                $intRecommendAmount = array_sum($arrRecommendAmount);
                if($intRecommendAmount < $tmp['distribute_amount']) {
                    $tmp['differences_amount']  = $tmp['distribute_amount']-$intRecommendAmount;
                }
            }
            $list[] = $tmp;

        }
        return $list;
    }

    /**
     * 判断参数&无开启库区库位时添加库区库位
     * @param array $arrPickupSkus
     * @param int   $intWarehouseId
     * @return  array
     * @throws Order_BusinessError
     */
    public function checkAndAssemblePickupSkusDetailInfo($arrPickupSkus, $intWarehouseId)
    {
        //是否开启库区库位
        $daoWarehouse = new Dao_Wrpc_Warehouse();
        $arrWarehouseInfo = $daoWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        $boolWarehouseLocationTag = $arrWarehouseInfo['storage_location_tag'];
        if (Order_Define_Warehouse::STORAGE_LOCATION_TAG_ENABLED == $boolWarehouseLocationTag) {
            foreach ($arrPickupSkus as $arrPickupSku) {
                foreach ($arrPickupSku['pick_infos'] as $arrPickupInfo) {
                    if (empty($arrPickupInfo['location_code'])) {
                        Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
                    }
                }
            }
        } else {
            foreach ($arrPickupSkus as $intKey => $arrPickupSku) {
                foreach ($arrPickupSku['pick_infos'] as $intPickInfosKey => $arrPickupInfo) {
                    if (empty($arrPickupInfo['location_code'])) {
                        $arrPickupSkus[$intKey]['pick_infos'][$intPickInfosKey]['location_code'] = Nscm_Define_Warehouse::VIRTURE_LOCATION_CODE_DEFAULT_STORE;
                    }
                }
            }
        }

        return $arrPickupSkus;
    }
}

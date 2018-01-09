<?php

/**
 * @name Service_Data_StockoutOrder
 * @desc 出库订单操作类
 * @author zhaozuowu@iwaimai.baidu.com　
 */
class Service_Data_StockoutOrder
{

    /**
     * orm obj
     * @var Model_Orm_StockoutOrder
     */
    protected $objOrmStockoutOrder;

    /**
     * orm obj
     * @var Model_Orm_StockoutOrderSku
     */
    protected $objOrmSku;

    /**
     * init
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();
        $this->objOrmSku = new Model_Orm_StockoutOrderSku();

    }


    /**
     * 获取下一步操作的出库单操作状态
     * @param $stockoutOrderStatus 出库单号
     * @return bool
     */
    public function getNextStockoutOrderStatus($stockoutOrderStatus)
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
     * 根据出库单号，更新出库单状态完成揽收
     * @param $strStockoutOrderId 出库单号
     * @return array
     * @throws Order_BusinessError
     */
    public function deliveryOrder($strStockoutOrderId)
    {
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        if (empty($strStockoutOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $stayRecevied = Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
        if ($stockoutOrderInfo['stockout_order_status'] != $stayRecevied) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        $nextStockoutOrderStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);//获取下一步操作状态
        if (empty($nextStockoutOrderStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($nextStockoutOrderStatus, $strStockoutOrderId, $stockoutOrderInfo) {
            $updateData = ['stockout_order_status' => $nextStockoutOrderStatus];
            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
            }
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }
            $objDaoRalStock = new Dao_Ral_Stock();
            $rs = $objDaoRalStock->adjustSkuStock($strStockoutOrderId, $stockoutOrderInfo['warehouse_id'], $arrStockoutDetail);
        });
        return [];
    }

    /**
     * 创建出库单
     * @param array $arrInput
     * @return bool
     */
    public function createStockoutOrder($arrInput)
    {
        $this->checkCreateParams($arrInput);
        $boolDuplicateFlag = $this->checkDuplicateOrder($arrInput['stockout_order_id']);
        Bd_Log::trace(sprintf("method[%s] stockout_order_id[%s]", __METHOD__, $arrInput['stockout_order_id']));
        if (false === $boolDuplicateFlag) {
            return [];
        }
        $boolCreateFlag = Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            Bd_Log::trace(sprintf("method[%s] arrCreateParams[%s]", __METHOD__, json_encode($arrCreateParams)));
            $objStockoutOrder = new Model_Orm_StockoutOrder();
            $objStockoutOrder->create($arrCreateParams, false);
            $this->createStockoutOrderSku($arrInput['skus']);
        });
        if (!$boolCreateFlag) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL);
        }
        return $boolCreateFlag;
    }

    /**
     * 创建出货单商品信息
     * @param array $arrSkus
     * @return bool
     */
    public function createStockoutOrderSku($arrSkus)
    {
        $arrBatchSkuCreateParams = $this->getBatchSkuCreateParams($arrSkus);
        if (empty($arrBatchSkuCreateParams)) {
            return false;
        }
        return Model_Orm_StockoutOrderSku::batchInsert($arrBatchSkuCreateParams, false);
    }

    /**
     * 校验订单是否已创建
     * @param integer $intOrderId
     * @return void
     */
    public function checkDuplicateOrder($intOrderId)
    {
        if (empty($intOrderId)) {
            return false;
        }
        $objStockoutOrder = Model_Orm_StockoutOrder::findOne(['stockout_order_id' => $intOrderId]);
        if ($objStockoutOrder) {
            return false;
        }
        return true;
    }

    /**
     * 校验业态订单参数
     * @param array
     * @return void
     */
    public function checkCreateParams($arrInput)
    {
        if ($arrInput['stockout_order_type']
            && !isset(Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrInput['stockout_order_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR);
        }
    }

    /**
     * 获取出库单创建参数
     * @param array $arrInput
     * @return array
     */
    public function getCreateParams($arrInput)
    {
        $arrCreateParams = [];
        if (empty($arrInput)) {
            return $arrCreateParams;
        }
        $arrCreateParams['stockout_order_status'] = Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS;
        if (!empty($arrInput['stockout_order_id'])) {
            $arrCreateParams['stockout_order_id'] = intval($arrInput['stockout_order_id']);
        }
        if (!empty($arrInput['business_form_order_id'])) {
            $arrCreateParams['business_form_order_id'] = intval($arrInput['business_form_order_id']);
        }
        if (!empty($arrInput['stockout_order_type'])) {
            $arrCreateParams['stockout_order_type'] = intval($arrInput['stockout_order_type']);
        }
        if (!empty($arrInput['warehouse_id'])) {
            $arrCreateParams['warehouse_id'] = intval($arrInput['warehouse_id']);
        }
        if (!empty($arrInput['stockout_order_remark'])) {
            $arrCreateParams['stockout_order_remark'] = strval($arrInput['stockout_order_remark']);
        }
        if (!empty($arrInput['customer_id'])) {
            $arrCreateParams['customer_id'] = intval($arrInput['customer_id']);
        }
        if (!empty($arrInput['customer_name'])) {
            $arrCreateParams['customer_name'] = strval($arrInput['customer_name']);
        }
        if (!empty($arrInput['customer_contactor'])) {
            $arrCreateParams['customer_contactor'] = strval($arrInput['customer_contactor']);
        }
        if (!empty($arrInput['customer_contact'])) {
            $arrCreateParams['customer_contact'] = strval($arrInput['customer_contact']);
        }
        if (!empty($arrInput['customer_address'])) {
            $arrCreateParams['customer_address'] = strval($arrInput['customer_address']);
        }
        return $arrCreateParams;
    }

    /**
     * 获取出库单商品创建参数
     * @param  array $arrSkus
     * @return array
     */
    public function getBatchSkuCreateParams($arrSkus)
    {
        $arrBatchSkuCreateParams = [];
        if (empty($arrSkus)) {
            return $arrBatchSkuCreateParams;
        }
        foreach ($arrSkus as $arrItem) {
            $arrSkuCreateParams = [];
            if (!empty($arrItem['sku_id'])) {
                $arrSkuCreateParams['sku_id'] = intval($arrItem['sku_id']);
            }
            if (!empty($arrItem['upc_id'])) {
                $arrSkuCreateParams['upc_id'] = strval($arrItem['upc_id']);
            }
            if (!empty($arrItem['order_amount'])) {
                $arrSkuCreateParams['order_amount'] = intval($arrItem['order_amount']);
            }
            $arrBatchSkuCreateParams[] = $arrSkuCreateParams;
        }
        return $arrBatchSkuCreateParams;
    }

    /**
     * 完成签收
     * @param $strStockoutOrderId 出库单号
     * @param $signupStatus 签收状态
     * @param $signupUpcs 签收数量
     * @return bool|mixed
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function finishorder($strStockoutOrderId, $signupStatus, $signupUpcs)
    {
        $res = [];
        $signupStatusList = Order_Define_StockoutOrder::SIGNUP_STATUS_LIST;
        $signupStatusList = array_keys($signupStatusList);
        if (!in_array($signupStatus, Order_Define_StockoutOrder::SIGNUP_STATUS_LIST) && empty($signupUpcs)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $status = Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS;
        if ($stockoutOrderInfo['stockout_order_status'] != $status) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($strStockoutOrderId, $signupStatus, $signupUpcs) {
            $updateData = ['signup_status' => $signupStatus];
            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
            }
            $res = [];
            if (empty($signupUpcs)) {
                return $res;
            }
            foreach ($signupUpcs as $item) {
                $condition = ['stockout_order_id' => $strStockoutOrderId, 'sku_id' => $item['sku_id']];
                $skuUpdata = ['upc_accept_amount' => $item['sku_accept_amount'], 'upc_reject_amount' => $item['sku_reject_amount']];
                $this->objOrmSku->updateStockoutOrderStatusByCondition($condition, $skuUpdata);
            }
        });
    }

    /**
     * get list search conditions by arrInput
     * @param array $arrInput
     * @return array
     */
    protected function getListConditions($arrInput)
    {
        $arrListConditions = [];
        if (!empty($arrInput['stockout_order_id'])) {
            $arrListConditions['stockout_order_id'] = intval($arrInput['stockout_order_id']);
        }
        if (!empty($arrInput['business_form_order_id'])) {
            $arrListConditions['business_form_order_id'] = intval($arrInput['business_form_order_id']);
        }
        if (!empty($arrInput['customer_name'])) {
            $arrListConditions['customer_name'] = ['like', $arrInput['customer_name'] . '%'];
        }
        if (!empty($arrInput['customer_id'])) {
            $arrListConditions['customer_id'] = intval($arrInput['customer_id']);
        }
        if (!empty($arrInput['is_print'])) {
            $arrListConditions['is_print'] = intval($arrInput['is_print']);
        }
        if (!empty($arrInput['stockout_order_status'])) {
            $arrListConditions['stockout_order_status'] = intval($arrInput['stockout_order_status']);
        }
        if (!empty($arrInput['start_time'])) {
            $arrListConditions['create_time'][] = ['>=', intval($arrInput['start_time'])];
        }
        if (!empty($arrInput['end_time'])) {
            $arrListConditions['create_time'][] = ['<=', intval($arrInput['end_time'])];
        }
        return $arrListConditions;
    }

    /**
     * 根据出库单号获取出库单信息及商品信息
     * @param int $strStockoutOrderId 出库单id
     * @return array
     */
    public function getOrderAndSkuListByStockoutOrderId($strStockoutOrderId)
    {
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $ret = [];
        if (empty($strStockoutOrderId)) {
            return $ret;
        }
        $arrOrderList = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);
        if (empty($arrOrderList)) {
            return $ret;
        }
        $objWarehouseRal = new Dao_Ral_Order_Warehouse();
        $arrWarehouseList = $objWarehouseRal->getWareHouseList($arrOrderList['warehouse_id']);
        $arrWarehouseList = !empty($arrWarehouseList) ? array_column($arrWarehouseList, null, 'warehouse_id') : [];
        $arrOrderList['warehouse_name'] = isset($arrWarehouseList[$arrOrderList['warehouse_id']]) ? $arrWarehouseList[$arrOrderList['warehouse_id']['warehouse_name']] : '';
        $skuList = $this->objOrmSku->getSkuInfoById($strStockoutOrderId);
        return [
            'stockout_order_info' => $arrOrderList,
            'stockout_order_sku' => $skuList,
        ];


    }

    /**
     * 完成拣货
     * @param $strStockoutOrderId
     * @param $pickupSkus
     * @return bool|mixed
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function finishPickup($strStockoutOrderId, $pickupSkus)
    {
        $res = [];
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }

        $status = Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS;
        if ($stockoutOrderInfo['stockout_order_status'] != $status) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }

        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($stockoutOrderInfo, $strStockoutOrderId, $pickupSkus) {
            $res = [];
            $stockoutOrderPickupAmount = 0;
            foreach ($pickupSkus as $item) {
                $stockoutOrderPickupAmount += $item['pickup_amount'];
            }
            $nextStockoutStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);
            $updateData = ['stockout_order_status' => $nextStockoutStatus, 'stockout_order_pickup_amount' => $stockoutOrderPickupAmount];
            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
            }
            $res = [];
            if (empty($pickupSkus)) {
                return $res;
            }
            foreach ($pickupSkus as $item) {
                $condition = ['stockout_order_id' => $strStockoutOrderId, 'sku_id' => $item['sku_id']];
                $skuUpdata = ['pickup_amount' => $item['pickup_amount']];
                $this->objOrmSku->updateStockoutOrderStatusByCondition($condition, $skuUpdata);
            }
        });
    }

    /**
     * get stockout order info list by page and conditions
     * @param array $arrInput
     * @return array
     */
    public function getStockoutOrderList($arrInput){
        $arrListConditions = $this->getListConditions($arrInput);
        $arrColumns = Model_Orm_StockoutOrder::getAllColumns();
        $intLimit = intval($arrInput['page_size']);
        $intOffset = (intval($arrInput['page_num']) - 1) * $intLimit;
        $arrRetList = Model_Orm_StockoutOrder::findRows($arrColumns, $arrListConditions, ['id' => 'asc'], $intOffset, $intLimit);
        return $arrRetList;
    }

    /**
     * get stockout order list
     * @param array $arrInput
     * @return array
     */
    public function getStockoutOrderCount($arrInput) {
        $arrListConditions = $this->getListConditions($arrInput);
        $arrColumns = Model_Orm_StockoutOrder::getAllColumns();
        return Model_Orm_StockoutOrder::count($arrListConditions);
    }

    /**
     * append sku info to order list
     * @param array $arrRetList
     * @return array
     */
    public function appendSkusToOrderList($arrRetList)
    {
        if (empty($arrRetList)) {
            return [];
        }
        $arrOrderIds = array_column($arrRetList, 'stockout_order_id');
        $arrOrderSkuList = Model_Orm_StockoutOrderSku::getStockoutOrderSkusByOrderIds($arrOrderIds);
        $arrMapOrderIdToSkus = Order_Util_Util::arrayToKeyValue($arrOrderSkuList, 'stockout_order_id');
        foreach ($arrRetList as $intKey => $arrRetItem) {
            $intOrderId = $arrRetItem['order_id'];
            if (!$intOrderId || !isset($arrMapOrderIdToSkus[$intOrderId])) {
                continue;
            }
            $arrRetList[$intKey]['skus'] = $arrMapOrderIdToSkus[$intOrderId];
        }
        return $arrRetList;
    }


    /**
     * 作废出库单
     * @param $strStockoutOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function deleteStockoutOrder($strStockoutOrderId)
    {

        $res = [];
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $allowOrderStatus = [
            Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS,
            Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS,
            Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS,
        ];
        if (!in_array($stockoutOrderInfo['stockout_order_status'], $allowOrderStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        $updateData = [
            'stockout_order_status' => Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS,
            'destroy_order_status' => $stockoutOrderInfo['stockout_order_status'],
        ];
        $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
        if (empty($result)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        //释放库存(已出库不释放库存)
        if ($stockoutOrderInfo['stockout_order_status'] >= Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
            return [];
        }

        return [];
    }

    /**
     * 出库单状态统计
     * @param $warehouseIds
     * @return array
     */
    public function getStockoutOrderStatisticalInfo($warehouseIds)
    {
        $ret = [];
        $warehouseIds = is_array($warehouseIds) ? $warehouseIds : explode(",", $warehouseIds);
        if (empty($warehouseIds)) {
            return $ret;
        }
        //待拣货条件
        $arrforPickinConditions = [
            'warehouse_id' => ['in', $warehouseIds],
            'stockout_order_status' => Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS,
        ];
        //待揽收条件
        $arrStayConditions = [
            'warehouse_id' => ['in', $warehouseIds],
            'stockout_order_status' => Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS,
        ];
        $forPickingAmount = $this->objOrmStockoutOrder->count($arrforPickinConditions);
        $forPickingAmount = intval($forPickingAmount) > 99 ? '99+' : intval($forPickingAmount);
        $stayLanshouAmount = $this->objOrmStockoutOrder->count($arrStayConditions);
        $stayLanshouAmount = intval($stayLanshouAmount) > 99 ? '99+' : intval($stayLanshouAmount);
        $list = [
            'for_picking_amount' => $forPickingAmount,
            'stay_lan_shou_amount' => $stayLanshouAmount,
        ];
        return $list;

    }

    /**
     * 获取出库单日志表
     * @param $strStockoutOrderId
     */
    public function getLogList($strStockoutOrderId)
    {
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $appId = Order_Define_StockoutOrder::APP_NWMS_ORDER_APP_ID;
        $condtion = [
            'app_id' => $appId,
            'log_type' => Order_Define_StockoutOrder::APP_NWMS_ORDER_LOG_TYPE, 'quota_idx_int_1' => $strStockoutOrderId,
            'page_size' => 20
        ];
        $list = Nscm_Service_OperationLog::getLogList($condtion);
        return $list;


    }

    /**
     * 过滤出库单前缀
     * @param $strStockoutOrderId
     * @return string
     */
    private function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        return ltrim($strStockoutOrderId, 'SSO');
    }

    /**
     *
     * @param array $arrStockoutOrderIds
     * @return array
     */
    private function batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds) {
        foreach((array)$arrStockoutOrderIds as $intKey => $strStockoutOrderId) {
            $arrStockoutOrderIds[$intKey] = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        }
        return $arrStockoutOrderIds;
    }

    /**
     * 获取出库单取消状态 
     * @return integer
     * @throws Order_BusinessError
     */
    public function getCancelStatus($strStockoutOrderId) {
        $intStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $objOrmStockoutOrder = Model_Orm_StockoutOrder::findOne(['stockout_order_id' => $intStockoutOrderId]);
        if (!$objOrmStockoutOrder) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        if (Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS < $objOrmStockoutOrder->stockout_order_status) {
            return Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_CANCEL;
        }
        return Order_Define_StockoutOrder::STOCKOUT_ORDER_NOT_CANCEL;
    }

    /**
     * 获取打印查询条件
     * @param array 
     * @return array
     */
    protected function getPrintConditions($arrStockoutOrderIds) {
        $arrStockoutOrderIds = $this->batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds);
        $arrConditions = [
            'stockout_order_id' => ['in', $arrStockoutOrderIds],
        ];
        return $arrConditions;
    }
    
    /**
     * 获取分拣打印列表
     * @param array $arrStockoutOrderIds
     * @return array
     */
    public function getOrderPrintList($arrStockoutOrderIds) {
        if (empty($arrStockoutOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR);            
        }
        $arrColumns = Model_Orm_StockoutOrder::getAllColumns();
        $arrConditions = $this->getPrintConditions($arrStockoutOrderIds);
        $arrRetList = Model_Orm_StockoutOrder::findRows($arrColumns, $arrConditions);
        return $this->appendSkusToOrderList($arrRetList);
    }

    /**
     * 获取总拣货打印列表
     * @param array $arrStockoutOrderIds
     * @return void
     */
    public function getSkuPrintList($arrStockoutOrderIds) {
        if (empty($arrStockoutOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR);            
        }
        $arrRet = [];
        $arrRet['order_amount'] = count($arrStockoutOrderIds);
        $arrConditions = $this->getPrintConditions($arrStockoutOrderIds);
        $arrRet['skus'] = Model_Orm_StockoutOrderSku::find($arrConditions)
        ->select(['sku_id', 'sku_name', 'upc_unit', 'sum(pickup_amount) as pickup_amount'])
        ->groupBy(['sku_id'])
        ->orderBy(['id' => 'asc'])
        ->rows();
        return $arrRet;
    }

}
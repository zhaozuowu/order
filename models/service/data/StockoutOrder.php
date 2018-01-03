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
            Bd_Log::warning(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }

        $stayRecevied = Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
        if ($stockoutOrderInfo['stockout_order_status'] != $stayRecevied) {
            Bd_Log::warning(__METHOD__ . ' no allow update stockout_order_status become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);

        }
        $nextStockoutOrderStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);//获取下一步操作状态
        if (empty($nextStockoutOrderStatus)) {
            Bd_Log::warning(__METHOD__ . ' update stockout_order_status fail  become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        $updateData = ['stockout_order_status' => $nextStockoutOrderStatus];
        $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
        if (empty($result)) {
            Bd_Log::warning(__METHOD__ . ' update stockout_order_status fail  become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
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
        if (false === $boolDuplicateFlag) {
            return [];
        }
        $boolCreateFlag = Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
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
        if ($objOrmStockoutOrder) {
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
     * 完成揽收
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
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
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
                $condition = ['stockout_order_id' => $strStockoutOrderId, 'upc_id' => $item['upc_id']];
                $skuUpdata = ['upc_accept_amount' => $item['upc_accept_amount'], 'upc_reject_amount' => $item['upc_reject_amount']];
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
            $arrListConditions['create_time'] = ['>=', intval($arrInput['start_time'])];
        }
        if (!empty($arrInput['end_time'])) {
            $arrListConditions['create_time'] = ['<=', intval($arrInput['end_time'])];
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
    public function getStockoutOrderListAndCount($arrInput)
    {
        $arrListConditions = $this->getListConditions($arrInput);
        $arrColumns = Model_Orm_StockoutOrder::getAllColumns();
        list($arrRetList, $intTotal) = Model_Orm_StockoutOrder::findRowsAndTotalCount($arrColumns, $arrListConditions, ['id' => 'asc']);
        $arrRetList = $this->appendSkusToOrderList($arrRetList);
        return [
            'total' => $intTotal,
            'orders' => $arrRetList,
        ];
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
        $arrOrderSkuList = Model_Orm_StockinOrderSku::getStockoutOrderSkusByOrderIds($arrOrderIds);
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
     * 过滤出库单前缀
     * @param $strStockoutOrderId
     * @return string
     */
    private function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        return ltrim($strStockoutOrderId, 'SSO');
    }

}
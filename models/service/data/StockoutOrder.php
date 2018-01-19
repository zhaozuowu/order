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
     * @var Dao_Ral_Order_Warehouse
     */
    protected  $objWarehouseRal;

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
     * stockout order redis
     * @var Dao_Redis_StockoutOrder
     */
    protected $objDaoRedisStockoutOrder;

    /**
     * stockout order log
     * @var Dao_Ral_Log
     */

    /**
     * dao ral stock
     * @var Dao_Ral_Stock
     */
    protected $objRalStock;

    /**
     * init
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();
        $this->objOrmSku = new Model_Orm_StockoutOrderSku();
        $this->objDaoRedisStockoutOrder = new Dao_Redis_StockoutOrder();
        $this->objRalStock = new Dao_Ral_Stock();
        $this->objWarehouseRal = new Dao_Ral_Order_Warehouse();
        $this->objRalLog = new Dao_Ral_Log();
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
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'distribute_amount', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }
            $arrStockoutDetail = $this->formatUnfreezeSkuStock($arrStockoutDetail);
            $rs = $this->objRalStock->unfreezeSkuStock($strStockoutOrderId, $stockoutOrderInfo['warehouse_id'], $arrStockoutDetail);
            if (empty($rs)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL);
            }
        });
        return [];
    }


    /**
     * @param $arrStockoutDetail
     * @return array
     */
    public function formatDeleteStockoutOrder($arrStockoutDetail) {

        $skuList = [];
        foreach ($arrStockoutDetail as $key => $item) {
            $row['sku_id'] = $item['sku_id'];
            $row['frozen_amount'] = $item['order_amount'];
            $row['stockout_amount'] = 0;
            $skuList[] = $row;
        }
        return $skuList;
    }
    /**
     * 格式话订单商品库存-解冻-接口
     * @param $arrStockoutDetail
     * @return array
     */
    public function formatUnfreezeSkuStock($arrStockoutDetail)
    {
        $skuList = [];
        foreach ($arrStockoutDetail as $key => $item) {
            $row['sku_id'] = $item['sku_id'];
            $row['frozen_amount'] = intval($item['distribute_amount']);
            $row['stockout_amount'] =intval($item['pickup_amount']);
            $skuList[] = $row;
        }
        return $skuList;
    }

    /**
     * 创建出库单
     * @param array $arrInput
     * @return mixed
     * @throws Order_BusinessError
     */
    public function createStockoutOrder($arrInput)
    {
        $this->checkCreateParams($arrInput);
        $boolDuplicateFlag = $this->checkDuplicateOrder($arrInput['stockout_order_id']);
        if (false === $boolDuplicateFlag) {
            return false;
        }
        Bd_Log::trace(sprintf("method[%s] skus[%s]", __METHOD__, $arrInput['skus']));
        Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            Bd_Log::trace(sprintf("method[%s] arrCreateParams[%s]", __METHOD__, json_encode($arrCreateParams)));
            $objStockoutOrder = new Model_Orm_StockoutOrder();
            $objStockoutOrder->create($arrCreateParams, false);
            $this->createStockoutOrderSku($arrInput['skus'], $arrCreateParams['stockout_order_id']);
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $userName = empty($arrInput['_session']['user_name']) ? '':$arrInput['_session']['user_name'];
            $operatorId =empty($arrInput['_session']['user_id']) ? 0 :intval($arrInput['_session']['user_id']);
            $this->addLog($operatorId, $userName, '创建出库单', $operationType, $arrInput['stockout_order_id']);
        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
                                            Order_Statistics_Type::ACTION_CREATE,
                                            $arrInput['stockout_order_id']);
    }

    /**
     * 创建出货单商品信息
     * @param array $arrSkus
     * @param integer $intStockoutOrderId
     * @return bool
     */
    public function createStockoutOrderSku($arrSkus, $intStockoutOrderId)
    {
        $arrBatchSkuCreateParams = $this->getBatchSkuCreateParams($arrSkus, $intStockoutOrderId);
        if (empty($arrBatchSkuCreateParams)) {
            return false;
        }
        return Model_Orm_StockoutOrderSku::batchInsert($arrBatchSkuCreateParams, false);
    }

    /**
     * 校验订单是否已创建
     * @param integer $intOrderId
     * @return bool
     */
    public function checkDuplicateOrder($intOrderId)
    {
        if (empty($intOrderId)) {
            return false;
        }
        $objStockoutOrder = Model_Orm_StockoutOrder::findOne(['stockout_order_id' => $intOrderId]);
        if ($objStockoutOrder) {
            Bd_Log::warning(sprintf("method[%s] duplicate order id", __METHOD__));
            return false;
        }
        return true;
    }

    /**
     * 校验业态订单参数
     * @param array
     * @return void
     * @throws Order_BusinessError
     */
    public function checkCreateParams($arrInput)
    {
        if ($arrInput['stockout_order_type']
            && !isset(Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrInput['stockout_order_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR);
        }
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function assembleStockoutOrder($arrInput) {
        //校验重复提交的问题
        /*if ($this->objDaoRedisStockoutOrder->getValByCustomerId($arrInput['customer_id'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_REPEAT_SUBMIT);
        }*/
        $intStockoutOrderId = Order_Util_Util::generateStockoutOrderId();
        $this->objDaoRedisStockoutOrder->setCustomerId($arrInput['customer_id']);
        $arrInput['stockout_order_id'] = $intStockoutOrderId;
        $arrInput['stockout_order_type'] = Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_STOCK;
        return $arrInput;
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
        $arrCreateParams['stockout_order_id'] = empty($arrInput['stockout_order_id']) ?
                                                    0 : intval($arrInput['stockout_order_id']);
        $arrCreateParams['shelf_info'] = empty($arrInput['shelf_info']) ?
                                                    '' : strval($arrInput['shelf_info']);
        $arrCreateParams['business_form_order_id'] = empty($arrInput['business_form_order_id']) ?
                                                        0 : intval($arrInput['business_form_order_id']);
        $arrCreateParams['stockout_order_type'] = empty($arrInput['stockout_order_type']) ? 0 : intval($arrInput['stockout_order_type']);
        $arrCreateParams['warehouse_id'] = empty($arrInput['warehouse_id']) ? 0 : intval($arrInput['warehouse_id']);
        $arrCreateParams['warehouse_name'] = empty($arrInput['warehouse_name']) ? '' : strval($arrInput['warehouse_name']);
        $arrCreateParams['stockout_order_remark'] = empty($arrInput['stockout_order_remark']) ? '' : strval($arrInput['stockout_order_remark']);
        $arrCreateParams['customer_id'] = empty($arrInput['customer_id']) ? 0 : intval($arrInput['customer_id']);
        $arrCreateParams['customer_name'] = empty($arrInput['customer_name']) ? '' : strval($arrInput['customer_name']);
        $arrCreateParams['customer_contactor'] = empty($arrInput['customer_contactor']) ? '' : strval($arrInput['customer_contactor']);
        $arrCreateParams['customer_contact'] = empty($arrInput['customer_contact']) ? '' : strval($arrInput['customer_contact']);
        $arrCreateParams['customer_address'] = empty($arrInput['customer_address']) ? '' : strval($arrInput['customer_address']);
        $arrCreateParams['customer_location'] = empty($arrInput['customer_location']) ? '' : strval($arrInput['customer_location']);
        $arrCreateParams['customer_location_source'] = empty($arrInput['customer_location_source']) ? 0 : intval($arrInput['customer_location_source']);
        $arrCreateParams['customer_city_id'] = empty($arrInput['customer_city_id']) ? 0 : intval($arrInput['customer_city_id']);
        $arrCreateParams['customer_city_name'] = empty($arrInput['customer_city_name']) ? '' : strval($arrInput['customer_city_name']);
        $arrCreateParams['customer_region_id'] = empty($arrInput['customer_region_id']) ? 0 : intval($arrInput['customer_region_id']);
        $arrCreateParams['customer_region_name'] = empty($arrInput['customer_region_name']) ? '' : strval($arrInput['customer_region_name']);
        $arrCreateParams['expect_arrive_start_time'] = empty($arrInput['expect_arrive_time']['start']) ?
                                                            0 : intval($arrInput['expect_arrive_time']['start']);
        $arrCreateParams['expect_arrive_end_time'] = empty($arrInput['expect_arrive_time']['end']) ?
                                                        0 : intval($arrInput['expect_arrive_time']['end']);
        $arrCreateParams['stockout_order_amount'] = empty($arrInput['stockout_order_amount']) ? 0 : intval($arrInput['stockout_order_amount']);
        $arrCreateParams['stockout_order_distribute_amount'] = empty($arrInput['stockout_order_distribute_amount']) ?
                                                                0 : intval($arrInput['stockout_order_distribute_amount']);
        $arrCreateParams['executor'] = empty($arrInput['executor']) ? '' : strval($arrInput['executor']);
        $arrCreateParams['executor_contact'] = empty($arrInput['executor_contact']) ? '' : strval($arrInput['executor_contact']);
        $arrCreateParams['stockout_order_source'] = empty($arrInput['business_form_order_type']) ? 0 : intval($arrInput['business_form_order_type']);
        $arrCreateParams['stockout_order_remark'] = empty($arrInput['stockout_order_remark']) ? '' : strval($arrInput['business_form_order_remark']);
        return $arrCreateParams;
    }

    /**
     * 获取出库单商品创建参数
     * @param  array $arrSkus
     * @param  integer $intStockoutOrderId
     * @return array
     */
    public function getBatchSkuCreateParams($arrSkus, $intStockoutOrderId)
    {
        $arrBatchSkuCreateParams = [];
        if (empty($arrSkus)) {
            return $arrBatchSkuCreateParams;
        }
        foreach ($arrSkus as $arrItem) {
            $arrSkuCreateParams = [];
            $arrSkuCreateParams['sku_id'] = empty($arrItem['sku_id']) ? 0 : intval($arrItem['sku_id']);
            $arrSkuCreateParams['order_amount'] = empty($arrItem['order_amount']) ? 0 : intval($arrItem['order_amount']);
            $arrSkuCreateParams['distribute_amount'] = empty($arrItem['distribute_amount']) ? 0 : intval($arrItem['distribute_amount']);
            $arrSkuCreateParams['sku_name'] = empty($arrItem['sku_name']) ? '' : strval($arrItem['sku_name']);
            $arrSkuCreateParams['upc_id'] = empty($arrItem['upc_id']) ? '' : strval($arrItem['upc_id']);
            $arrSkuCreateParams['upc_unit'] = empty($arrItem['upc_unit']) ? 0 : intval($arrItem['upc_unit']);
            $arrSkuCreateParams['upc_unit_num'] = empty($arrItem['upc_unit_num']) ? 0 : intval($arrItem['upc_unit_num']);
            $arrSkuCreateParams['sku_net'] = empty($arrItem['sku_net']) ? '' : strval($arrItem['sku_net']);
            $arrSkuCreateParams['sku_net_unit'] = empty($arrItem['sku_net_unit']) ? 0 : intval($arrItem['sku_net_unit']);
            $arrSkuCreateParams['sku_effect_type'] = empty($arrItem['sku_effect_type']) ? 0 : intval($arrItem['sku_effect_type']);
            $arrSkuCreateParams['sku_effect_day'] = empty($arrItem['sku_effect_day']) ? 0 : intval($arrItem['sku_effect_day']);
            $arrSkuCreateParams['cost_price'] = empty($arrItem['cost_price']) ? 0 : intval($arrItem['cost_price']);
            $arrSkuCreateParams['cost_total_price'] = empty($arrItem['cost_total_price']) ? 0 : intval($arrItem['cost_total_price']);
            $arrSkuCreateParams['cost_price_tax'] = empty($arrItem['cost_price_tax']) ? 0 : intval($arrItem['cost_price_tax']);
            $arrSkuCreateParams['cost_total_price_tax'] = empty($arrItem['cost_total_price_tax']) ? 0 : intval($arrItem['cost_total_price_tax']);
            $arrSkuCreateParams['send_price'] = empty($arrItem['send_price']) ? 0 : intval($arrItem['send_price']);
            $arrSkuCreateParams['send_total_price'] = empty($arrItem['send_total_price']) ? 0 : intval($arrItem['send_total_price']);
            $arrSkuCreateParams['sku_business_form'] = empty($arrItem['sku_business_form']) ? '' : strval($arrItem['sku_business_form']);
            $arrSkuCreateParams['sku_tax_rate'] = empty($arrItem['sku_tax_rate']) ? 0 : intval($arrItem['sku_tax_rate']);
            $arrSkuCreateParams['stockout_order_id'] = $intStockoutOrderId;
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
        if (!empty($arrInput['warehouse_id'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_id']);
            $arrListConditions['warehouse_id'] = ['in', $arrWareHouseIds];
        }
        if (!empty($arrInput['stockout_order_id'])) {
            $arrListConditions['stockout_order_id'] = $this->trimStockoutOrderIdPrefix($arrInput['stockout_order_id']);
        }
        if (!empty($arrInput['business_form_order_id'])) {
            $arrListConditions['business_form_order_id'] = intval($arrInput['business_form_order_id']);
        }
        if (!empty($arrInput['customer_name'])) {
            $arrListConditions['customer_name'] = $arrInput['customer_name'];
        }
        if (!empty($arrInput['customer_id'])) {
            $arrListConditions['customer_id'] = intval($arrInput['customer_id']);
        }
        if (!empty($arrInput['is_print'])) {
            $arrListConditions['stockout_order_is_print'] = intval($arrInput['is_print']);
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
     * @throws Nscm_Exception_Error
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

        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($arrOrderList['warehouse_id']);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $arrWarehouseList = !empty($arrWarehouseList) ? array_column($arrWarehouseList, null, 'warehouse_id') : [];
        $arrOrderList['warehouse_name'] =!empty($arrOrderList['warehouse_name']) ? $arrOrderList['warehouse_name']:(isset($arrWarehouseList[$arrOrderList['warehouse_id']]) ? $arrWarehouseList[$arrOrderList['warehouse_id']]['warehouse_name']: '');
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
     * @param $userId
     * @param  $userName
     * @return bool|mixed
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function finishPickup($strStockoutOrderId, $pickupSkus,$userId,$userName)
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

        $tmp = $this->checkoutPuckAmount($pickupSkus);
        if ($tmp) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_AMOUNT_ERROR);
        }
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($stockoutOrderInfo, $strStockoutOrderId, $pickupSkus,$userId,$userName) {
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
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_UPDATE_SUCCESS;
            $this->addLog($userId, $userName, '完成拣货:'.$strStockoutOrderId.",拣货数量:".$stockoutOrderPickupAmount, $operationType, $strStockoutOrderId);
            $this->notifyTmsFnishPick($strStockoutOrderId,$pickupSkus);
        });
    }

    /**
     * get stockout order info list by page and conditions
     * @param array $arrInput
     * @return array
     */
    public function getStockoutOrderList($arrInput)
    {
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
    public function getStockoutOrderCount($arrInput)
    {
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
        $arrOrderSkuList = $this->objOrmSku->getStockoutOrderSkusByOrderIds($arrOrderIds);
        $arrMapOrderIdToSkus = Order_Util_Util::arrayToKeyValues($arrOrderSkuList, 'stockout_order_id');
        foreach ($arrRetList as $intKey => $arrRetItem) {
            $intOrderId = $arrRetItem['stockout_order_id'];
            if (!$intOrderId || !isset($arrMapOrderIdToSkus[$intOrderId])) {
                continue;
            }
            $arrRetList[$intKey]['skus'] = $arrMapOrderIdToSkus[$intOrderId];
        }
        return $arrRetList;
    }

    /**
     * 获取出库单sku列表
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockoutOrderSkus($arrInput)
    {
        if (empty($arrInput['stockout_order_id'])) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $arrInput['stockout_order_id'] = $this->trimStockoutOrderIdPrefix($arrInput['stockout_order_id']);
        $arrConditions = $this->getOrderSkuConditions($arrInput);
        return Model_Orm_StockoutOrderSku::getListByConditions($arrConditions, $arrInput['page_size'], $arrInput['page_num']);
    }

    /**
     * @param array $arrInput
     * @return integer
     * @throws Order_BusinessError
     */
    public function getStockoutOrderSkusCount($arrInput) {
        if (empty($arrInput['stockout_order_id'])) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $arrConditions = $this->getOrderSkuConditions($arrInput);
        return Model_Orm_StockoutOrderSku::count($arrConditions);
    }

    /**
     * 获取出库单sku列表的查询条件
     * @param array $arrInput
     * @return array
     */
    protected function getOrderSkuConditions($arrInput) {
        $arrConditions = [];
        if (!empty($arrInput['stockout_order_id'])) {
            $arrConditions['stockout_order_id'] = $arrInput['stockout_order_id'];
        }
        if (!empty($arrInput['sku_id'])) {
            $arrConditions['sku_id'] = $arrInput['sku_id'];
        }
        if (!empty($arrInput['upc_id'])) {
            $arrConditions['upc_id'] = $arrInput['upc_id'];
        }
        if (!empty($arrInput['sku_name'])) {
            $arrConditions['sku_name'] = ['like', $arrInput['sku_name'] . '%'];
        }
        return $arrConditions;
    }


    /**
     * 作废出库单
     * @param $strStockoutOrderId
     * @param $mark
     * @return array
     * @throws Order_BusinessError
     */
    public function deleteStockoutOrder($strStockoutOrderId,$mark,$userId,$userName)
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
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($strStockoutOrderId,$updateData,$stockoutOrderInfo,$mark,$userId,$userName) {

            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
            }
            //释放库存(已出库不释放库存)
            if ($stockoutOrderInfo['stockout_order_status'] >= Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
                return [];
            }
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'order_amount', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $this->addLog($userId, $userName, $mark, $operationType, $strStockoutOrderId);
            $this->notifyCancelfreezeskustock($strStockoutOrderId,$stockoutOrderInfo['warehouse_id']);
        });

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
            'page_size' => 100
        ];
        $list = Nscm_Service_OperationLog::getLogList($condtion);
        $list = empty($list['log_list']) ? []:$list['log_list'];
        return $list;


    }

    /**
     * 过滤出库单前缀
     * @param $strStockoutOrderId
     * @return string
     */
    public function trimStockoutOrderIdPrefix($strStockoutOrderId)
    {
        return ltrim($strStockoutOrderId, Nscm_Define_OrderPrefix::SOO);
    }

    /**
     *
     * @param array $arrStockoutOrderIds
     * @return array
     */
    public function batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds)
    {
        foreach ((array)$arrStockoutOrderIds as $intKey => $strStockoutOrderId) {
            $arrStockoutOrderIds[$intKey] = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        }
        return $arrStockoutOrderIds;
    }

    /**
     * 获取出库单取消状态
     * @return integer
     * @throws Order_BusinessError
     */
    public function getCancelStatus($strStockoutOrderId)
    {
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
    protected function getPrintConditions($arrStockoutOrderIds)
    {
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
    public function getOrderPrintList($arrStockoutOrderIds)
    {
        if (empty($arrStockoutOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR);
        }
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $arrConditions = $this->getPrintConditions($arrStockoutOrderIds);
        $arrRetList = $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        $arrRetList = $this->appendSkusToOrderList($arrRetList);
        $updateData = ['stockout_order_is_print'=>Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRINT];
        $this->objOrmStockoutOrder->updateDataByConditions($arrConditions,$updateData);
        return $this->appendSkusToOrderList($arrRetList);
    }

    /**
     * 获取总拣货打印列表
     * @param array $arrStockoutOrderIds
     * @return array
     * @throws Order_BusinessError
     */
    public function getSkuPrintList($arrStockoutOrderIds)
    {
        if (empty($arrStockoutOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR);
        }
        $arrRet = [];
        $arrRet['order_amount'] = count($arrStockoutOrderIds);
        $arrConditions = $this->getPrintConditions($arrStockoutOrderIds);
        $arrRet['skus'] = Model_Orm_StockoutOrderSku::getGroupList($arrConditions, 'sku_id');
        $updateData = ['stockout_order_is_print'=>Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRINT];
        $this->objOrmStockoutOrder->updateDataByConditions($arrConditions,$updateData);
        return $arrRet;
    }

    /**
     * 拣货数量检查
     * @param $pickupSkus
     * @return bool
     */
    public function checkoutPuckAmount($pickupSkus): bool
    {
        $tmp = true;
        foreach ($pickupSkus as $item) {
            if (!empty($item['pickup_amount'])) {
                $tmp = false;
                break;
            }
        }
        return $tmp;
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

        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId, 'pickup_skus' => $pickupSkus];
        $strCmd = Order_Define_Cmd::CMD_FINISH_PRICKUP_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL);
        }
        return [];
    }

    /**
     * 作废出库单（下游通知库存wmq）
     * @param $strStockoutOrderId
     * @param $warehouseId
     * @return array
     */
    private function notifyCancelfreezeskustock($strStockoutOrderId, $warehouseId)
    {
        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId,'warehouse_id'=>$warehouseId];
        $strCmd = Order_Define_Cmd::CMD_DELETE_STOCKOUT_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false === $ret) {
           Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
       }
       return [];
    }

    /**
     * 订单商品库存-作废-上游
     * @param $strStockoutOrderId
     * @param $warehouseId
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function cancelStockoutOrder($strStockoutOrderId, $warehouseId)
    {
        $rs = $this->objRalStock->cancelfreezeskustock($strStockoutOrderId, $warehouseId);
        if (empty($rs)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
        }
        return $rs;
    }

    /**
     * write log
     * @param $operatorId
     * @param $userName
     * @param $mark
     * @param $operationType
     * @param $quotaIdxInt1
     * @param $content
     */
    private function addLog($operatorId, $userName, $content, $operationType, $quotaIdxInt1)
    {
        $logType = Order_Define_StockoutOrder::APP_NWMS_ORDER_LOG_TYPE;
        $this->objRalLog->addLog($logType,$quotaIdxInt1,$operationType,$userName,$operatorId,$content);
    }


}
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
     * dao ral stock
     * @var Dao_Wrpc_Stock
     */
    protected $objWrpcStock;

    /**
     * dao ral stock
     * @var Dao_Huskar_Stock
     */
    protected $objHuskarStock;

    /**
     * dao ral sku
     * @var Dao_Ral_Sku
     */
    protected $objRalSKu;

    /**
     * dao wrpc tms
     * @var Dao_Wrpc_Tms
     */
    protected $objWrpcTms;

    /**
     * dao oms
     * @var Dao_Ral_Oms
     */
    protected $daoOms;

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
        $this->objWrpcTms = new Dao_Wrpc_Tms();
        $this->objRalSKu  = new Dao_Ral_Sku();
        $this->daoOms = new Dao_Ral_Oms();
        $this->objWrpcStock = new Dao_Wrpc_Stock();
    }

    /**
     * assemble sku info
     * @param array $arrSkuExtraInfo
     * @return array
     */
    public function assembleRecvSkuDetail($arrSkuExtraInfo)
    {
        $arrResult = [];
        if (!is_array($arrSkuExtraInfo)) {
            return $arrResult;
        }
        foreach ($arrSkuExtraInfo as $arrRow) {
            $arrInfo = [];
            if (is_array($arrRow['sku_extra_info'])) {
                foreach ($arrRow['sku_extra_info'] as $arrRowSkuExtraInfo) {
                    $arrInfo[] = [
                        'product_time' => intval($arrRowSkuExtraInfo['product_time']),
                        'expire_time' => intval($arrRowSkuExtraInfo['expire_time']),
                        'good_amount' => intval($arrRowSkuExtraInfo['good_amount']),
                        'defective_amount' => intval($arrRowSkuExtraInfo['defective_amount']),
                    ];
                }
            }
            $intSkuId = intval($arrRow['sku_id']);
            if (isset($arrResult[$intSkuId])) {
                Bd_Log::warning(sprintf('assemble recv sku detail sku_id repeat, old info[%s], new info[%s]',
                    json_encode($arrResult[$intSkuId]), json_encode($arrInfo)));
            }
            $arrResult[$intSkuId] = [
                'sku_id' => $intSkuId,
                'sku_extra_info' => $arrInfo,
            ];
        }
        return $arrResult;
    }

    /**
     * assemble oms delivery order
     * @param array $arrSkuInfo
     * @return array
     */
    private function assembleOmsDeliveryOrder($arrSkuInfo)
    {
        $arrRet = [];
        foreach ($arrSkuInfo as $arrRow) {
//            $intSkuAmount = 0;
            $arrSkuExtraList = [];
            foreach ($arrRow['sku_extra_info'] as $arrRowSkuExtraInfo) {
                $arrSkuExtraList[] = [
                    'amount' => $arrRowSkuExtraInfo['good_amount'] + $arrRowSkuExtraInfo['defective_amount'],
                    'expire_date' => intval($arrRowSkuExtraInfo['expire_time']),
                ];
//                $arrInfo[] = [
//                    'product_time' => intval($arrRowSkuExtraInfo['product_time']),
//                    'expire_time' => intval($arrRowSkuExtraInfo['expire_time']),
//                    'good_amount' => intval($arrRowSkuExtraInfo['good_amount']),
//                    'defective_amount' => intval($arrRowSkuExtraInfo['defective_amount']),
//                ];
//                $intSkuAmount += $arrRowSkuExtraInfo['good_amount'] + $arrRowSkuExtraInfo['defective_amount'];
            }
            $arrRet[] = [
                'sku_id' => $arrRow['sku_id'],
//                'sku_amount' => $intSkuAmount,
                'distribute_info' => $arrSkuExtraList,
            ];
        }
        return $arrRet;
    }

    /**
     * check stock status allow write db
     * @param int $intStockStatus
     * @return bool
     */
    private function checkStockStatusAllowWriteDb($intStockStatus)
    {
        switch (intval($intStockStatus)) {
            case Order_Define_StockoutOrder::STOCK_STATUS_SURE:
                Bd_log::warning('recv sku detail order repeat');
                return false;
            case Order_Define_StockoutOrder::STOCK_STATUS_UNSURE:
                Bd_Log::trace('recv sku detail stock status unsure');
                return true;
            case Order_Define_StockoutOrder::STOCK_STATUS_HISTORY:
            default:
                Bd_Log::warning('recv sku detail history data');
                return false;
        }
    }

    /**
     * @param $intStockoutOrderId
     * @param $arrStockoutSkuInfo
     * @throws Exception
     */
    public function recvSkuDetail($intStockoutOrderId, $arrStockoutSkuInfo)
    {
        $intStockoutOrderId = intval($intStockoutOrderId);
        $arrStockoutSkuInfo = $this->assembleRecvSkuDetail($arrStockoutSkuInfo);
        // check stockout order info
        $ormStockoutOrder = Model_Orm_StockoutOrder::getStockoutOrderObjByOrderId($intStockoutOrderId);
        if (empty($ormStockoutOrder)) {
            Bd_Log::warning('recv sku detail stockout id not exist: ' . $intStockoutOrderId);
            return;
        }
        // status
        if (!$this->checkStockStatusAllowWriteDb($ormStockoutOrder->stockout_order_stock_status))
        {
            return;
        }
        // notify oms, next version
        $daoOms = new Dao_Wrpc_Oms(Order_Define_Wrpc::OMS_NWMS_SERVICE_NAME);
        $intLogisticOrderId = $ormStockoutOrder->logistics_order_id;
        $intShipmentOrderId = $ormStockoutOrder->shipment_order_id;
        $daoOms->updateStockoutOrderSkuInfo($intLogisticOrderId, $intShipmentOrderId, $this->assembleOmsDeliveryOrder($arrStockoutSkuInfo));

        // write to db
        $arrOrmStockoutSku = Model_Orm_StockoutOrderSku::getAllStockoutSkuByStockoutId($intStockoutOrderId);
        Model_Orm_StockoutOrder::getConnection()->transaction(function ()
        use($intStockoutOrderId, $arrStockoutSkuInfo, $ormStockoutOrder, $arrOrmStockoutSku) {
            $ormStockoutOrder->updateStockoutOrderStockStatus(Order_Define_StockoutOrder::STOCK_STATUS_SURE);
            foreach ($arrOrmStockoutSku as $key => $ormStockSku) {
                if (isset($arrStockoutSkuInfo[$ormStockSku->sku_id])) {
                    $ormStockSku->updateSkuExtraInfo(Nscm_Lib_Util::jsonEncode(
                        $arrStockoutSkuInfo[$ormStockSku->sku_id]['sku_extra_info']));
                    unset($arrStockoutSkuInfo[$ormStockSku->sku_id]);
                } else {
                    // sku in db, but not in input param
                    Bd_Log::warning('recv sku detail sku in db but not in input param, sku_id:' . $ormStockSku->sku_id);
                }
            }
            if (!empty($arrStockoutSkuInfo)) {
                Bd_Log::warning('recv sku detail in input param but no int db: ' . json_encode($arrStockoutSkuInfo));
            }
        });

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
     * @throws Exception
     */
    public function deliveryOrder($strStockoutOrderId,$userId,$userName)
    {
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        if (empty($strStockoutOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        //状态已经修改返回成功
        if ($stockoutOrderInfo['stockout_order_status'] ==
            Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
            return [];
        }
        $stayRecevied = Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
        if ($stockoutOrderInfo['stockout_order_status'] != $stayRecevied) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        $nextStockoutOrderStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);//获取下一步操作状态
        if (empty($nextStockoutOrderStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
         Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($nextStockoutOrderStatus, $strStockoutOrderId, $stockoutOrderInfo,$userId,$userName) {
            $updateData = ['stockout_order_status' => $nextStockoutOrderStatus, 'destroy_order_status' => $stockoutOrderInfo['stockout_order_status']];
            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
            }
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'distribute_amount', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }
            $arrStockoutDetail = $this->formatUnfreezeSkuStock($arrStockoutDetail);
            $rs = $this->objWrpcStock->unfreezeSkuStock($strStockoutOrderId, $stockoutOrderInfo['warehouse_id'], $arrStockoutDetail);
//            $rs = $this->objRalStock->unfreezeSkuStock($strStockoutOrderId, $stockoutOrderInfo['warehouse_id'], $arrStockoutDetail);
            if (empty($rs)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL);
            }
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_UPDATE_SUCCESS;
            $userId = !empty($userId) ? $userId: Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = !empty($userName) ? $userName:Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            $this->addLog($userId, $userName, '完成揽收:'.$strStockoutOrderId,$operationType, $strStockoutOrderId);
        });
         Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            Order_Statistics_Type::ACTION_UPDATE,
            $strStockoutOrderId);//更新报表
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
            if(empty($item['distribute_amount']) ) {
                continue;
            }
            $row['sku_id'] = $item['sku_id'];
            $row['frozen_amount'] = intval($item['distribute_amount']);
            $row['stockout_amount'] =intval($item['pickup_amount']);
            $skuList[] = $row;
        }
        return $skuList;
    }

    /**
     * 过滤掉没有库存的商品
     * @param $arrSkus
     * @return mixed
     */
    protected function filterNoStockSku($arrSkus) {
        if (empty($arrSkus)) {
            return $arrSkus;
        }
        foreach ((array)$arrSkus as $intKey => $arrSkuItem) {
            if (0 == $arrSkuItem['distribute_amount']) {
                unset($arrSkus[$intKey]);
            }
        }
        return $arrSkus;
    }

    /**
     *
     * 创建出库单
     * @param array $arrInput
     * @return mixed
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function createStockoutOrder($arrInput)
    {
        $this->checkCreateParams($arrInput);
        $boolDuplicateFlag = $this->checkDuplicateOrder($arrInput['stockout_order_id']);
        if (false === $boolDuplicateFlag) {
            return false;
        }
        $arrInput['skus'] = $this->filterNoStockSku($arrInput['skus']);
        $arrInput['shipment_order_id'] = $this->objWrpcTms->createShipmentOrder($arrInput);
        Bd_Log::trace(sprintf("method[%s] skus[%s]", __METHOD__, json_encode($arrInput['skus'])));
        Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            Bd_Log::trace(sprintf("method[%s] arrCreateParams[%s]", __METHOD__, json_encode($arrCreateParams)));
            $objStockoutOrder = new Model_Orm_StockoutOrder();
            $objStockoutOrder->create($arrCreateParams, false);
            $this->createStockoutOrderSku($arrInput['skus'], $arrCreateParams['stockout_order_id']);
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $userName = empty($arrInput['_session']['user_name']) ? Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME:$arrInput['_session']['user_name'];
            $operatorId =empty($arrInput['_session']['user_id']) ? Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID :intval($arrInput['_session']['user_id']);
            $this->addLog($operatorId, $userName, '创建出库单', $operationType, $arrInput['stockout_order_id']);
            if (Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_OMS == $arrInput['data_source']) {
                $intShipmentOrderId = intval($arrInput['shipment_order_id']);
                $intStockoutOrderId = intval($arrInput['stockout_order_id']);
                $intBusinessOrderId = intval($arrInput['business_form_order_id']);
                $arrOmsSkus = $this->formatOmsSku($arrInput['skus']);
                $this->daoOms->updateOmsOrderInfo($intBusinessOrderId, $intStockoutOrderId,
                    $intShipmentOrderId, $arrOmsSkus);
            }

        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
                                            Order_Statistics_Type::ACTION_CREATE,
                                            $arrInput['stockout_order_id']);
        return true;
    }

    /**
     * format oms sku
     * @param array[] $arrSkus
     * @return array[]
     */
    private function formatOmsSku($arrSkus)
    {
        $arrRet = [];
        foreach ($arrSkus as $arrSku) {
            $arrRet[] = [
                'sku_id' => intval($arrSku['sku_id']),
                'sku_amount' => intval($arrSku['distribute_amount']),
            ];
        }
        return $arrRet;
    }

    /**
     * 获取订单的操作信息
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getWarehouseLocation($warehouseId) {
        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($warehouseId);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        if (empty($arrWarehouseList)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED);
        }
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $warehouseLocation  = empty($arrWarehouseList[$warehouseId]) ? '':$arrWarehouseList[$warehouseId]['location'];
        return $warehouseLocation;
    }

    /**
     * 根据仓库id获取仓库的地址
     * @param $strWarehouseId
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getWarehouseAddrById($strWarehouseId) {
        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($strWarehouseId);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        if (empty($arrWarehouseList)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED);
        }
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $strWarehouseAddr  = empty($arrWarehouseList[$strWarehouseId]) ? '' : $arrWarehouseList[$strWarehouseId]['address'];
        return $strWarehouseAddr;
    }

    /**
     * 手动创建出库单
     * @param $arrInput
     */
    public function createStockoutOrderByManual($arrInput)
    {
        $list = ['message'=>''];
        $arrInput = $this->assembleShipmentOrderInfo($arrInput);
        $objDsSku = new Service_Data_Sku();
        $dataBussniessObj = new  Service_Data_BusinessFormOrder();
        $skuTotalNum = count($arrInput['skus']);
        $originSkuIds = array_column($arrInput['skus'],'sku_id');
        $arrInput['skus'] = $objDsSku->appendSkuInfosToSkuParams($arrInput['skus'],$arrInput['business_form_order_type'],false);
        $arrInput['business_form_order_status'] =  Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_SUCCESS;
        $arrInput = $dataBussniessObj->checkSkuBusinessForm($arrInput);
        if (Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_FAILED == $arrInput['business_form_order_status']) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR);
        }
        list($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail) = $dataBussniessObj->getFreezeStockParams($arrInput);
        $this->objHuskarStock = new Dao_Huskar_Stock();
        $arrStockRet = $this->objHuskarStock->freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail);
        $arrStockSkus = $arrStockRet['result'];
        if(empty($arrStockSkus) || empty($arrInput)) {
            Bd_Log::warning(sprintf("checkSkuStock failed stockoutOrderId[%s]",
                $intStockoutOrderId));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL);
        }
        $arrInput = $dataBussniessObj->appendStockSkuInfoToOrder($arrInput, $arrStockSkus);
        $arrInput = $dataBussniessObj->appendSkuTotalAmountToOrder($arrInput);
        if (empty($arrInput) || empty($arrInput['skus'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL);
        }
        if($skuTotalNum != count($arrInput['skus'])) {
            $assertSkuIds = array_column($arrInput['skus'],'sku_id');
            $arrDiffSkuIds = array_diff($originSkuIds,$assertSkuIds);
            $arrDiffSkuIds = implode(",",$arrDiffSkuIds);
            $list['message'] ='以下商品不存在所选仓库'.$arrDiffSkuIds;
        }
        //异步创建出库单
        $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER, $arrInput,
            strval($arrInput['stockout_order_id']));
        if (false === $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error",
                __METHOD__, Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER));
        }
        return $list;
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
     * 校验重复提交
     * @param string $strCustomerId
     * @package string $strLogisticsOrderId
     * @return mixed
     * @throws Order_BusinessError
     */
    public function checkRepeatSubmit($strLogisticsOrderId) {
        $arrStockoutOrderInfo = $this->objDaoRedisStockoutOrder->getCacheStockoutInfoByLogisticsOrderId($strLogisticsOrderId);
        if (!empty($arrStockoutOrderInfo)) {
            return $arrStockoutOrderInfo;
        }
        $arrStockoutOrderInfo = $this->getStockoutInfoByLogisticsOrderId($strLogisticsOrderId);
        if (!empty($arrStockoutOrderInfo)) {
            return $arrStockoutOrderInfo;
        }
    }

    /**
     * 缓存出库单需要返回的数据
     * @param array $arrInput
     * @return void
     */
    public function cacheStockoutInfo($arrInput) {
        $arrRetStockoutInfo = Order_Define_Format::formatStockoutInfo($arrInput);
        $intLogisticsOrderId = $arrInput['logistics_order_id'];
        $this->objDaoRedisStockoutOrder->setCacheStockoutInfo($intLogisticsOrderId, $arrRetStockoutInfo);
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function assembleStockoutOrder($arrInput) {
        $intStockoutOrderId = Order_Util_Util::generateStockoutOrderId();
        $arrInput['stockout_order_id'] = $intStockoutOrderId;
        Order_Exception_Collector::setOrderIdAll($intStockoutOrderId);
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
        $arrInput['shelf_info']['devices'] = (object)$arrInput['shelf_info']['devices'];
        $arrCreateParams['stockout_order_status'] = Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS;
        $arrCreateParams['logistics_order_id'] = empty($arrInput['logistics_order_id']) ?
                                                    0 : intval($arrInput['logistics_order_id']);
        $arrCreateParams['stockout_order_id'] = empty($arrInput['stockout_order_id']) ?
                                                    0 : intval($arrInput['stockout_order_id']);
        $arrCreateParams['shipment_order_id'] = empty($arrInput['shipment_order_id']) ?
                                                    0 : intval($arrInput['shipment_order_id']);
        $arrCreateParams['shelf_info'] = empty($arrInput['shelf_info']) ?
                                                    '' : json_encode($arrInput['shelf_info']);
        $arrCreateParams['business_form_order_id'] = empty($arrInput['business_form_order_id']) ?
                                                        0 : intval($arrInput['business_form_order_id']);
        $arrCreateParams['stockout_order_type'] = empty($arrInput['stockout_order_type']) ? 0 : intval($arrInput['stockout_order_type']);
        $arrCreateParams['warehouse_id'] = empty($arrInput['warehouse_id']) ? 0 : intval($arrInput['warehouse_id']);
        $arrCreateParams['warehouse_name'] = empty($arrInput['warehouse_name']) ? '' : strval($arrInput['warehouse_name']);
        $arrCreateParams['stockout_order_remark'] = empty($arrInput['stockout_order_remark']) ? '' : strval($arrInput['stockout_order_remark']);
        $arrCreateParams['customer_id'] = empty($arrInput['customer_id']) ? '' : strval($arrInput['customer_id']);
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
        $arrCreateParams['stockout_order_remark'] = empty($arrInput['business_form_order_remark']) ? '' : strval($arrInput['business_form_order_remark']);
        $arrCreateParams['stockout_order_total_price'] = empty($arrInput['stockout_order_total_price']) ?
                                                            0 : intval($arrInput['stockout_order_total_price']);
        if(!empty($arrInput['expect_arrive_start_time'])) {
            $arrCreateParams['expect_arrive_start_time'] = intval($arrInput['expect_arrive_start_time']);
        }
        if(!empty($arrInput['expect_arrive_end_time'])) {
            $arrCreateParams['expect_arrive_end_time'] = intval($arrInput['expect_arrive_end_time']);
        }
        $arrCreateParams['data_source'] = $arrInput['data_source'] ?? 0;
        $arrCreateParams['stockout_order_stock_status'] = Order_Define_StockoutOrder::STOCK_STATUS_UNSURE;
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
            $arrSkuCreateParams['send_upc_num'] = empty($arrItem['send_upc_num']) ? 0 : intval($arrItem['send_upc_num']);
            $arrSkuCreateParams['sku_net'] = empty($arrItem['sku_net']) ? '' : strval($arrItem['sku_net']);
            $arrSkuCreateParams['sku_net_unit'] = empty($arrItem['sku_net_unit']) ? 0 : intval($arrItem['sku_net_unit']);
            $arrSkuCreateParams['sku_effect_type'] = empty($arrItem['sku_effect_type']) ? 0 : intval($arrItem['sku_effect_type']);
            $arrSkuCreateParams['sku_effect_day'] = empty($arrItem['sku_effect_day']) ? 0 : intval($arrItem['sku_effect_day']);
            $arrSkuCreateParams['cost_price'] = empty($arrItem['cost_price']) ? 0 : intval($arrItem['cost_price']);
            $arrSkuCreateParams['cost_total_price'] = empty($arrItem['cost_total_price']) ? 0 : intval($arrItem['cost_total_price']);
            $arrSkuCreateParams['cost_price_tax'] = empty($arrItem['cost_price_tax']) ? 0 : intval($arrItem['cost_price_tax']);
            $arrSkuCreateParams['cost_total_price_tax'] = empty($arrItem['cost_total_price_tax']) ? 0 : intval($arrItem['cost_total_price_tax']);
            $arrSkuCreateParams['send_price'] = empty($arrItem['send_price']) ? 0 : intval($arrItem['send_price']);
            $arrSkuCreateParams['send_price_tax'] = empty($arrItem['send_price_tax']) ? 0 : intval($arrItem['send_price_tax']);
            $arrSkuCreateParams['send_total_price'] = empty($arrItem['send_total_price']) ? 0 : intval($arrItem['send_total_price']);
            $arrSkuCreateParams['send_total_price_tax'] = empty($arrItem['send_total_price_tax']) ?
                                                            0 : intval($arrItem['send_total_price_tax']);
            $arrSkuCreateParams['sku_business_form'] = empty($arrItem['sku_business_form']) ? '' : strval($arrItem['sku_business_form']);
            $arrSkuCreateParams['sku_category_text'] = empty($arrItem['sku_category_text']) ? '' : strval($arrItem['sku_category_text']);
            $arrSkuCreateParams['sku_tax_rate'] = empty($arrItem['sku_tax_rate']) ? 0 : intval($arrItem['sku_tax_rate']);
            $arrSkuCreateParams['import'] = empty($arrItem['import']) ? 0 : intval($arrItem['import']);
            $arrSkuCreateParams['stockout_order_id'] = $intStockoutOrderId;
            $arrBatchSkuCreateParams[] = $arrSkuCreateParams;
        }
        return $arrBatchSkuCreateParams;
    }

    /**
     * 完成签收
     * @param $strStockoutOrderId 出库单号
     * @param $intSignupStatus 签收状态
     * @param $arrSignupSkus 签收数量
     * @return bool|mixed
     * @throws Exception
     * @throws Order_BusinessError
     */
    public function finishorder($strStockoutOrderId, $intSignupStatus, $arrSignupSkus)
    {
        if (empty(Order_Define_StockoutOrder::SIGNUP_STATUS_LIST[$intSignupStatus])) {
            Bd_Log::warning("signup_status is not in right status by:".$intSignupStatus.",stockoutOrder:".$strStockoutOrderId);
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        $strStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning("stockcoutOrderInfo no data:by stockoutOrderId：".$strStockoutOrderId);
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $status = Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS;
        if (array_key_exists($stockoutOrderInfo['signup_status'],Order_Define_StockoutOrder::STOCKOUT_SIGINUP_STATUS_LIST)) {
            return [];
        }
        if ($stockoutOrderInfo['stockout_order_status'] != $status) {
            Bd_Log::warning("stockoutOrderInfo can't modify stockout_order_status by stockoutOrderId:".$strStockoutOrderId);
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use
                                            ($strStockoutOrderId, $intSignupStatus, $arrSignupSkus,$stockoutOrderInfo) {
            $updateData = ['signup_status' => $intSignupStatus];
            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
            }

            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_UPDATE_SUCCESS;
            $userId = Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            $this->addLog($userId, $userName, '完成签收:'.$strStockoutOrderId,$operationType, $strStockoutOrderId);
            $res = [];
            if (empty($arrSignupSkus) && ($intSignupStatus != Order_Define_StockoutOrder::STOCKOUT_SIGINUP_REJECT_ALL) &&
                $stockoutOrderInfo['stockout_order_source'] == Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_SHELF ) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SIGNUP_SKUS_NOT_EXISTS);
            }elseif(empty($arrSignupSkus) && ($intSignupStatus == Order_Define_StockoutOrder::STOCKOUT_SIGINUP_ACCEPT_ALL) &&
                $stockoutOrderInfo['stockout_order_source'] != Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_SHELF ) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SIGNUP_SKUS_NOT_EXISTS);
            }
            if ($intSignupStatus == Order_Define_StockoutOrder::STOCKOUT_SIGINUP_REJECT_ALL) {

                $condition = ['stockout_order_id' => $strStockoutOrderId];
                $ormSkuInfo = $this->objOrmSku->findAll($condition);
                foreach($ormSkuInfo as $skuInfo) {
                   $skuInfo->upc_accept_amount = 0;
                   $skuInfo->upc_reject_amount = $skuInfo->pickup_amount;
                   $skuInfo->update();
                }
                return [];

            }
            foreach ($arrSignupSkus as $arrSignupSkusItem) {
                $intSkuId = intval(array_keys($arrSignupSkusItem)[0]);
                $intAmount = intval($arrSignupSkusItem[$intSkuId]);
                $condition = ['stockout_order_id' => $strStockoutOrderId, 'sku_id' => $intSkuId];
                $skuInfo =  $this->objOrmSku->findOne($condition);
                $skuAcceptAmount = $intAmount;
                $skuRejectAmount = 0;
                if ($intSignupStatus == Order_Define_StockoutOrder::STOCKOUT_SIGINUP_ACCEPT_ALL) {
                    $skuRejectAmount = 0;
                    $skuAcceptAmount = $skuInfo->pickup_amount;
                }elseif($intSignupStatus == Order_Define_StockoutOrder::STOCKOUT_SIGINUP_REJECT_ALL) {
                    $skuAcceptAmount = 0;
                    $skuRejectAmount = $skuInfo->pickup_amount;
                }elseif($intSignupStatus == Order_Define_StockoutOrder::STOCKOUT_SIGINUP_ACCEPT_PART) {
                    $skuAcceptAmount = $intAmount;
                    $skuRejectAmount = !empty($skuInfo->pickup_amount) ? ($skuInfo->pickup_amount - $skuAcceptAmount):$skuRejectAmount;
                }
                $skuUpdata = ['upc_accept_amount' => $skuAcceptAmount, 'upc_reject_amount' => $skuRejectAmount];
                $this->objOrmSku->updateStockoutOrderStatusByCondition($condition, $skuUpdata);
            }
        });
    }

    /**
     * get list search conditions by arrInput
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    protected function getListConditions($arrInput)
    {
        $arrListConditions = [];
        if (!empty($arrInput['warehouse_ids'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_ids']);
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
            $arrListConditions['customer_id'] = strval($arrInput['customer_id']);
        }
        if (!empty($arrInput['is_print'])) {
            $arrListConditions['stockout_order_is_print'] = intval($arrInput['is_print']);
        }
        if (!empty($arrInput['stockout_order_status'])) {
            $arrListConditions['stockout_order_status'] = intval($arrInput['stockout_order_status']);
        }

        if (!empty($arrInput['logistics_order_id'])) {
            $arrListConditions['logistics_order_id'] = $arrInput['logistics_order_id'];
        }
        if (!empty($arrInput['stockout_order_source'])) {
            $arrListConditions['stockout_order_source'] = $arrInput['stockout_order_source'];
        }
        if (!empty($arrInput['start_time'])) {
            $arrListConditions['create_time'][] = ['>=', intval($arrInput['start_time'])];
        }
        if (!empty($arrInput['end_time'])) {
            $arrListConditions['create_time'][] = ['<=', intval($arrInput['end_time'])];
        }

        if (!empty($arrInput['shipment_order_id'])) {
            $arrListConditions['shipment_order_id'] = intval($arrInput['shipment_order_id']);
        }
        if (!empty($arrInput['data_source'])) {
            // 检查查询的数据来来源类型是否正确
            $arrListConditions['data_source']= ['in',[Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_MANUAL_INPUT]];
            if($arrInput['data_source'] != Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_MANUAL_INPUT) {
                $arrListConditions['data_source']= ['in', [Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_SYSTEM_ORDER,Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_OMS]];
            }
        }
        if (isset(Order_Define_StockoutOrder::PICKUP_ORDER_TYPE_MAP[$arrInput['is_pickup_ordered']])){
            $arrListConditions['is_pickup_ordered'] = intval($arrInput['is_pickup_ordered']);
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
     * 根据出库单号获取出库单信息
     * @param int $arrStockoutOrderIds 出库单id
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getOrderDetailByStockoutOrderIds($arrStockoutOrderIds)
    {
        $arrStockoutOrderIds = $this->batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds);
        $ret = [];
        if (empty($arrStockoutOrderIds)) {
            return $ret;
        }
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $arrConditions = $this->getPrintConditions($arrStockoutOrderIds);
        $arrRetList = $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        if (empty($arrRetList)) {
            return $ret;
        }

        return $arrRetList;
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
            Bd_Log::warning("stockcoutOrderInfo no data:by stockoutOrderId：".$strStockoutOrderId);
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }

        $status = Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS;
        if ($stockoutOrderInfo['stockout_order_status'] != $status) {
            Bd_Log::warning("stockoutOrderInfo can't modify stockout_order_status by stockoutOrderId:".$strStockoutOrderId);
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);
        }
        $tmp = $this->checkoutPuckAmount($pickupSkus);
        if ($tmp) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_AMOUNT_ERROR);
        }
        $transaction =  Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($stockoutOrderInfo, $strStockoutOrderId, $pickupSkus,$userId,$userName) {
            $res = [];
            $stockoutOrderPickupAmount = 0;
            foreach ($pickupSkus as $item) {
                $stockoutOrderPickupAmount += $item['pickup_amount'];
            }
            $nextStockoutStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);
            $updateData = ['stockout_order_status' => $nextStockoutStatus, 'stockout_order_pickup_amount' => $stockoutOrderPickupAmount,'destroy_order_status' => $stockoutOrderInfo['stockout_order_status']];
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
            $userId = !empty($userId) ? $userId: Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = !empty($userName) ? $userName:Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            $this->addLog($userId, $userName, '完成拣货:'.$strStockoutOrderId.",拣货数量:".$stockoutOrderPickupAmount, $operationType, $strStockoutOrderId);
            $this->notifyTmsFnishPick($strStockoutOrderId,$pickupSkus);

        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            Order_Statistics_Type::ACTION_UPDATE,
            $strStockoutOrderId);//更新报表
        return $transaction;
    }

    /**
     * 获取配货商品列表
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getDistributionSkuList($arrInput)
    {
        $retArr = ['list'=>[]];
        $warehouseId = intval($arrInput['warehouse_id']);
        $arrIds = is_array($arrInput['ids']) ? $arrInput['ids']:explode(",",$arrInput['ids']);
        $ret = $this->objRalSKu->getSkuInfosByIds($arrIds);
        if(empty($ret) || !empty($ret['error_no'])) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_GET_SKUINFO_FAIL,'以下编码在彩云中找不到对应商品:'.$ret['error_msg']);
        }
        $arrSkuList = $ret['result']['skus'];
        //$arrSkuList = $this->formatPageinate($arrInput['page_size'],$arrInput['page_num'],$arrSkuList);
        $arrSkuIds = array_column($arrSkuList,'sku_id');
        $arrStockInfo = $this->objRalStock->getStockInfo($warehouseId,$arrSkuIds);
        $arrStockInfo = empty($arrStockInfo) ? []: array_column($arrStockInfo,null,'sku_id');
        foreach($arrSkuList as $key=>$item) {
            $upsList = !empty($item['min_upc']) ? $item['min_upc']:[];
            $arrSkuList[$key]['upc_unit'] = !empty($upsList['upc_unit']) ? $upsList['upc_unit']:0;
            $arrSkuList[$key]['upc_ids'] = !empty($item['upc_ids']) ? $item['upc_ids']:[];
            $arrSkuList[$key]['min_upc_id'] = !empty($upsList['upc_id']) ? $upsList['upc_id']:0;
            $arrSkuList[$key]['upc_unit_num'] = !empty($upsList['upc_unit_num']) ? $upsList['upc_unit_num']:0;
            $arrSkuList[$key]['available_amount'] = isset($arrStockInfo[$item['sku_id']]) ? $arrStockInfo[$item['sku_id']]['available_amount']:0;
        }
        $retArr['list'] = $arrSkuList;
        return $retArr;

    }

    /**
     * 系统确认作废出库单
     * @param $strStockoutOrderId
     * @param $remark
     * @return array
     * @throws Order_BusinessError
     */
    public function confirmCancelStockoutOrder($strStockoutOrderId,$remark)
    {
        $res = [];
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息
        if (empty($stockoutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        if ($stockoutOrderInfo['stockout_order_status'] == Order_Define_StockoutOrder::STOCKOUT_ORDER_DESTROYED) {
            return $res;
        }
        if($stockoutOrderInfo['stockout_order_pre_cancel'] != Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRE_CANCEL) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_PRE_CANCEL_ERROR);
        }

        if($stockoutOrderInfo['is_pickup_ordered'] == Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_IS_PICKUP_ORDERED);
        }
        $updateData = [
            'stockout_order_status' => Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS,
            'destroy_order_status' => $stockoutOrderInfo['stockout_order_status'],
        ];
        Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($strStockoutOrderId,$updateData,$stockoutOrderInfo,$remark) {

            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
            }
            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $userId = Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            $mark = '作废出库单:'.$remark;
            //释放库存(已出库不释放库存)
            if ($stockoutOrderInfo['stockout_order_status'] >= Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
                return [];
            }
            $this->addLog($userId, $userName, $mark, $operationType, $strStockoutOrderId);
            $this->notifyCancelfreezeskustock($strStockoutOrderId,$stockoutOrderInfo['warehouse_id']);
        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            Order_Statistics_Type::ACTION_UPDATE,
            $strStockoutOrderId);//更新报表

        return [];
    }

    /**
     * format
     * @param $pageSize
     * @param $pageNum
     * @param $arrSkuList
     * @return array
     */
    private  function formatPageinate($pageSize, $pageNum, $arrSkuList)
    {
        $arrList = [];
        if (empty($arrSkuList)) return $arrList;
        $intLimit = intval($pageSize);
        $intOffset = (intval($pageNum) - 1) * $intLimit;
        $arrSkuList = array_slice($arrSkuList, $intOffset, $intLimit);
        return $arrSkuList;

    }

    /*
     * @param $arrStockoutOrderIds
     * @param $userId
     * @param $userName
     * @throws Order_BusinessError
     */
    public function batchFinishPickup($arrStockoutOrderIds, $userId, $userName)
    {
        $res = [];
        $totalPickupNum = count($arrStockoutOrderIds);
        $arrStockoutOrderIds = $this->batchTrimStockoutOrderIdPrefix($arrStockoutOrderIds);
        $arrConditions = [
            'stockout_order_id' => ['in', $arrStockoutOrderIds],
        ];
        $arrColumns = $this->objOrmStockoutOrder->getAllColumns();
        $stockoutOrderList= $this->objOrmStockoutOrder->findRows($arrColumns, $arrConditions);
        if (empty($stockoutOrderList)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $dealPickupNum = count($stockoutOrderList);
        $dealPickupNum = $totalPickupNum == $dealPickupNum ? $totalPickupNum:($dealPickupNum);
        $failPickupNum = 0;
        $status = Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS;
        foreach($stockoutOrderList as $stockoutOrderInfo) {
            try{
                $strStockoutOrderId = $stockoutOrderInfo['stockout_order_id'];
                $arrConditions = ['stockout_order_id' => $strStockoutOrderId];
                $arrColumns = $this->objOrmSku->getAllColumns();
                $skuList = $this->objOrmSku->findRows($arrColumns, $arrConditions);
                if (empty($skuList)) {
                    Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
                }
                $pickupSkus = $this->appendSkuDistributeAmount($skuList);
                $this->finishPickup($strStockoutOrderId,$pickupSkus,$userId,$userName);
            }catch (Exception $e) {
                $dealPickupNum --;
                $failPickupNum ++;
                continue;
            }

        }
//        if ($dealPickupNum == 0) {
//            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL);
//        }
        return ['successPickNum'=>$dealPickupNum,'failPickupNum'=>$failPickupNum];
    }

    /**
     * 拼接获取下单数量
     * @param $skuList
     * @return array
     */
    public function appendSkuDistributeAmount($skuList) {
        if (empty($skuList)) {
            return [];
        }
        $list = [];
        foreach($skuList as $key=>$item) {
             $tmp['sku_id'] = $item['sku_id'];
             $tmp['pickup_amount'] = $item['distribute_amount'];
             $list[] = $tmp;
        }
        return $list;
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
        $arrRetList = Model_Orm_StockoutOrder::findRows($arrColumns, $arrListConditions, ['update_time' => 'desc'], $intOffset, $intLimit);
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
     * 获取出库单状态
     * @param $strStockoutOrderId
     * @return integer
     * @throws Order_BusinessError
     */
    public function getStockoutOrderStatus($strStockoutOrderId) {
        if (empty($strStockoutOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        $intStockoutOrderId = $this->trimStockoutOrderIdPrefix($strStockoutOrderId);
        $objStockoutOrder = Model_Orm_StockoutOrder::findOne([
            'stockout_order_id' => $intStockoutOrderId,
        ]);
        if (!$objStockoutOrder) {
            Order_BusinessError::throwException(Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST);
        }
        return $objStockoutOrder->stockout_order_status;
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
        $arrInput['stockout_order_id'] = $this->trimStockoutOrderIdPrefix($arrInput['stockout_order_id']);
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
        if (Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED == $stockoutOrderInfo['is_pickup_ordered']) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_IN_PICKING);
        }
        $updateData = [
            'stockout_order_status' => Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS,
            'destroy_order_status' => $stockoutOrderInfo['stockout_order_status'],
        ];
        Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($strStockoutOrderId,$updateData,$stockoutOrderInfo,$mark,$userId,$userName) {

            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
            }

            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $userId = !empty($userId) ? $userId: Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = !empty($userName) ? $userName:Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            $mark = '作废出库单:'.$mark;
            $this->addLog($userId, $userName, $mark, $operationType, $strStockoutOrderId);
            //释放库存(已出库不释放库存)
            if ($stockoutOrderInfo['stockout_order_status'] >= Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
                return [];
            }
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'order_amount', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }

            $this->notifyCancelfreezeskustock($strStockoutOrderId,$stockoutOrderInfo['warehouse_id']);
        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            Order_Statistics_Type::ACTION_UPDATE,
            $strStockoutOrderId);//更新报表

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
     * @param string $strStockoutOrderId
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
     * @throws Order_BusinessError
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
        $arrRet['pickup_amount'] = $this->getTotalPickupAmount($arrRet['skus']);
        $updateData = ['stockout_order_is_print'=>Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRINT];
        $this->objOrmStockoutOrder->updateDataByConditions($arrConditions,$updateData);
        return $arrRet;
    }

    /**
     * 获取拣货数量总数
     * @param $arrSkus
     * @return int
     */
    protected function getTotalPickupAmount($arrSkus) {
        if (empty($arrSkus)) {
            return 0;
        }
        $intTotalPickupAmount = 0;
        foreach ((array)$arrSkus as $arrSkuItem) {
            $intTotalPickupAmount += intval($arrSkuItem['pickup_amount']);
        }
        return $intTotalPickupAmount;
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
        $intShipmentOrderId = Model_Orm_StockoutOrder::
                                getShipmentOrderIdByStockoutOrderId($strStockoutOrderId);
        $arrStockoutParams = [
            'stockout_order_id' => strval($strStockoutOrderId),
            'shipment_order_id' => strval($intShipmentOrderId),
            'pickup_skus' => $pickupSkus
        ];
        $strCmd = Order_Define_Cmd::CMD_FINISH_PRICKUP_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false == $ret) {
            Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL);
        }
        return [];
    }

    /**
     * 调用tms接口通知拣货数量
     * @param integer $intShipmentOrderId
     * @param array $arrPickupSkus
     * @return void
     * @throws Order_BusinessError
     */
    public function syncNotifyTmsFinishPickup($intShipmentOrderId, $arrPickupSkus){
        $this->objWrpcTms->notifyPickupAmount($intShipmentOrderId, $arrPickupSkus);
    }

    /**
     * 作废出库单（下游通知库存wmq）
     * @param $strStockoutOrderId
     * @param $warehouseId
     * @return array
     * @throws Order_BusinessError
     */
    private function notifyCancelfreezeskustock($strStockoutOrderId, $warehouseId)
    {
        $arrStockoutParams = ['stockout_order_id' => $strStockoutOrderId,'warehouse_id'=>$warehouseId];
        $strCmd = Order_Define_Cmd::CMD_DELETE_STOCKOUT_ORDER;
        $ret = Order_Wmq_Commit::sendWmqCmd($strCmd, $arrStockoutParams, $strStockoutOrderId);
        if (false === $ret) {
           Bd_Log::warning(sprintf("method[%s] cmd[%s] error", __METHOD__, $strCmd));
           Order_BusinessError::throwException(Order_Error_Code::SEND_CMD_FAILED);
       }
       return [];
    }

    /**
     * 订单商品库存-作废-上游
     * @param $strStockoutOrderId
     * @param $warehouseId
     * @return bool
     * @throws Order_BusinessError
     */
    public function cancelStockoutOrder($strStockoutOrderId, $warehouseId)
    {
        $rs = $this->objWrpcStock->cancelFreezeSkuStock($strStockoutOrderId, $warehouseId);
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

    /**
     * 根据客户id获取客户信息
     * @param $customerId
     * @return array
     */
    public function getCustomerInfoById($customerId)
    {
        return isset(Order_Define_StockoutOrder::CUSTOMER_LIST[$customerId]) ? Order_Define_StockoutOrder::CUSTOMER_LIST[$customerId]:[];
    }

    /**
     * 查询客户名称sug
     * @return array
     */
    public function getCustomernameSug()
    {
        $customerList = Order_Define_StockoutOrder::CUSTOMER_LIST;
        return $customerList;


    }
	
    /**
     * 通过物流单号获取出库单信息
     * @param $strLogisticsOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function getStockoutInfoByLogisticsOrderId($strLogisticsOrderId) {
        if (empty($strLogisticsOrderId)) {
            return [];
        }
        $arrRet = Model_Orm_StockoutOrder::getStockoutOrderInfoByLogisticsOrderId($strLogisticsOrderId);
        if (empty($arrRet)) {
            return [];
        }
        $arrStockoutOrderInfo = $arrRet[0];
        $arrStockoutOrderInfo['skus'] = $this->objOrmSku->getSkuInfoById($arrStockoutOrderInfo['stockout_order_id']);
        return $arrStockoutOrderInfo;
    }

    /**
     * 手机无人货架信息
     * @param $arrInput
     * @return mixed
     */
    private function assembleShipmentOrderInfo($arrInput)
    {
        $startTime = $arrInput['expect_arrive_start_time'];
        $endTime = $arrInput['expect_arrive_end_time'];
        $nowTime = time();
        if (($startTime-$nowTime<=Order_Define_Const::HALF_AN_HOUR_FORMAT_SECONDS) || ($startTime>=$endTime)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR,'参数异常');
        }
        $arrInput['warehouse_location'] = $this->getWarehouseLocation($arrInput['warehouse_id']);
        $customerList = $this->getCustomerInfoById($arrInput['customer_id']);
        if (empty($customerList)) {
            return $arrInput;
        }
        $arrInput['shelf_info'] = $customerList['shelf_info'];
        $arrInput['business_form_order_remark'] = empty($arrInput['stockout_order_remark']) ? '' : strval($arrInput['stockout_order_remark']);
        $arrInput['logistics_order_id'] = empty($arrInput['logistics_order_id']) ? 0 : intval($arrInput['logistics_order_id']);
        $arrInput['expect_arrive_time']['start'] = empty($arrInput['expect_arrive_start_time']) ? 0 : $arrInput['expect_arrive_start_time'];
        $arrInput['expect_arrive_time']['end'] = empty($arrInput['expect_arrive_end_time']) ? 0 : $arrInput['expect_arrive_end_time'];
        $arrInput['executor'] = empty($customerList['executor']) ? '' : strval($customerList['executor']);
        $arrInput['executor_contact'] = empty($customerList['executor_contact']) ? '' : strval($customerList['executor_contact']);

        $arrInput['customer_location'] = empty($customerList['customer_location']) ? '':$customerList['customer_location'];
        $arrInput['customer_region_id'] = empty($customerList['customer_region_id']) ? '' : strval($customerList['customer_region_id']);
        $arrInput['customer_city_id'] = empty($customerList['customer_city_id']) ? '' : intval($customerList['customer_city_id']);
        $arrInput['customer_city_name'] = empty($customerList['customer_city_name']) ? '' : strval($customerList['customer_city_name']);
        $arrInput['customer_region_name'] = empty($customerList['customer_region_name']) ? '' : strval($customerList['customer_region_name']);
        $arrInput['customer_location_source'] = empty($customerList['customer_location_source']) ?  : intval($customerList['customer_location_source']);
        return $arrInput;
    }

    /**
     * 预取消出库单
     * @param  integer $intStockOutOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function preCancelOrder($intStockOutOrderId)
    {
        $objStockOutOrderInfo = Model_Orm_StockoutOrder::getOrderInfoObjectById($intStockOutOrderId);
        if (empty($objStockOutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $intStockOutOrderStatus = $objStockOutOrderInfo->stockout_order_status;
        $intStockOutOrderIsPrint = $objStockOutOrderInfo->stockout_order_is_print;
        $intPickupOrderd = $objStockOutOrderInfo->is_pickup_ordered;
        $intStockOutOrderPreCancel = $objStockOutOrderInfo->stockout_order_pre_cancel;
        $intStockOutOrderCancelType = $objStockOutOrderInfo->stockout_order_cancel_type;
        if (Order_Define_StockoutOrder::PICKUP_ORDERE_IS_CREATED == $intPickupOrderd
            && Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS != $intStockOutOrderStatus) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_IS_PICKUP_ORDERED);
        }
        if ($intStockOutOrderCancelType == Order_Define_StockoutOrder::STOCKOUT_ORDER_CANCEL_TYPE_DEFAULT
            && $intStockOutOrderPreCancel == Order_Define_StockoutOrder::STOCKOUT_ORDER_DEFAULT_PRE_CANCEL
            && Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS != $intStockOutOrderStatus) {
            $objStockOutOrderInfo->updatePreCancelType(
                Order_Define_StockoutOrder::STOCKOUT_ORDER_CANCEL_TYPE_SYS,
                Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRE_CANCEL
            );
        }
        return [];
    }

    /**
     * rollback cancel order
     * @param $intStockOutOrderId
     * @return array
     * @throws Order_BusinessError
     */
    public function rollbackCancelOrder($intStockOutOrderId)
    {
        $intStockOutOrderId = intval($intStockOutOrderId);
        $ormStockOutOrderInfo = Model_Orm_StockoutOrder::getOrderInfoObjectById($intStockOutOrderId);
        if (empty($ormStockOutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        if (Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS != $ormStockOutOrderInfo->stockout_order_status
                && Order_Define_StockoutOrder::STOCKOUT_ORDER_IS_PRE_CANCEL == $ormStockOutOrderInfo->stockout_order_pre_cancel
                && Order_Define_StockoutOrder::STOCKOUT_ORDER_CANCEL_TYPE_SYS == $ormStockOutOrderInfo->stockout_order_cancel_type) {
            Bd_Log::trace('rollback cancel stockout order, order id: ' . $intStockOutOrderId);
            $ormStockOutOrderInfo->updatePreCancelType(
                Order_Define_StockoutOrder::STOCKOUT_ORDER_CANCEL_TYPE_DEFAULT,
                Order_Define_StockoutOrder::STOCKOUT_ORDER_DEFAULT_PRE_CANCEL);
        } else {
            // @alarm
            Bd_Log::warning('rollback cancel stockout order something wrong, order info: '
                . json_encode($ormStockOutOrderInfo->toArray()));
        }
        return [];
    }

    /**
     * 批量根据仓库id获取仓库的地址
     * @param $arrWarehouseIds
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function getWarehouseAddrByIds($arrWarehouseIds) {
        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($arrWarehouseIds);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        if (empty($arrWarehouseList)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED);
        }
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $arrWarehouseAddr = [];
        foreach ($arrWarehouseList as $warehouseInfo) {
            $arrWarehouseAddr[$warehouseInfo['warehouse_id']] = empty($warehouseInfo['address'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : $warehouseInfo['address'];
        }
        return $arrWarehouseAddr;
    }

    /**
     * 批量完成拣货单出库单的完成
     * @param int    $intPickupOrderId 拣货单id
     * @param array  $arrPickupSkus
     * @param int    $userId
     * @param string $userName
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function batchFinishOrder($intPickupOrderId, $arrPickupSkus, $userId, $userName)
    {
        $arrStockoutOrderPickupList = $this->assembleStockoutOrderSkuList($intPickupOrderId, $arrPickupSkus);
        foreach ($arrStockoutOrderPickupList as $arrStockoutOrderPickupInfo) {
            $intStockoutOrderId = $arrStockoutOrderPickupInfo['stockout_order_id'];
            $arrPickupStockOrderSkus = $arrStockoutOrderPickupInfo['pickup_skus'];
            $stockoutOrderPickupAmount = array_sum(array_column($arrPickupStockOrderSkus, 'pickup_amount'));
            $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($intStockoutOrderId);//获取出库订单信息
            $nextStockoutStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);
            $updateData = [
                'stockout_order_status' => $nextStockoutStatus,
                'stockout_order_pickup_amount' => $stockoutOrderPickupAmount,
                'destroy_order_status' => $stockoutOrderInfo['stockout_order_status'],
            ];
            Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($intStockoutOrderId, $updateData, $arrPickupStockOrderSkus){
                $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($intStockoutOrderId, $updateData);
                if (empty($result)) {
                    Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
                }
                foreach ($arrPickupStockOrderSkus as $item) {
                    $condition = [
                        'stockout_order_id' => $intStockoutOrderId,
                        'sku_id' => $item['sku_id'],
                    ];
                    $skuUpdateData = ['pickup_amount' => $item['pickup_amount']];
                    $this->objOrmSku->updateStockoutOrderStatusByCondition($condition, $skuUpdateData);
                }
                $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_UPDATE_SUCCESS;
                $userId = !empty($userId) ? $userId: Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
                $userName = !empty($userName) ? $userName:Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
                $this->addLog($userId, $userName, '完成揽收:'.$intStockoutOrderId,$operationType, $intStockoutOrderId);
                $this->notifyTmsFnishPick(strval($intStockoutOrderId), $arrPickupStockOrderSkus);
            });
           //更新报表
            Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
                Order_Statistics_Type::ACTION_UPDATE,
                $intStockoutOrderId);
        }

    }

    /**
     * 拼装出库单拣货所需参数
     * @param $intPickupOrderId
     * @param $arrPickupSkus
     * @return array
     * @throws Order_BusinessError
     */
    private function assembleStockoutOrderSkuList($intPickupOrderId, $arrPickupSkus)
    {
        $arrPickupSkusMap = [];
        foreach ($arrPickupSkus as $arrPickupSku) {
            if (isset($arrPickupSkusMap[$arrPickupSku['sku_id']])) {
                $arrPickupSkusMap[$arrPickupSku['sku_id']] += $arrPickupSku['pickup_amount'];
                continue;
            }
            $arrPickupSkusMap[$arrPickupSku['sku_id']] = $arrPickupSku['pickup_amount'];
        }
        $arrStockouOrderIds = Model_Orm_StockoutPickupOrder::getStockoutOrderIdsByPickupOrderId($intPickupOrderId);
        if (empty($arrStockouOrderIds)) {
            Order_BusinessError::throwException(Order_Error_Code::PICKUP_ORDER_SKUS_NOT_EXISTED);
        }
        $objOrmSku = new Model_Orm_StockoutOrderSku();

        $arrStockouOrderSkuPickupMap = [];
        $arrStockouOrderSkuPickupList = [];
        $arrStockouOrderSkuList = $objOrmSku->getStockoutOrderSkusByOrderIds($arrStockouOrderIds);
        foreach ($arrStockouOrderSkuList as $arrStockoutOrderSku) {
            $intStockoutOrderId = $arrStockoutOrderSku['stockout_order_id'];
            $intSkuId = $arrStockoutOrderSku['sku_id'];
            $intSkuDistributeAmount = $arrStockoutOrderSku['distribute_amount'];
            if (empty($arrPickupSkusMap[$intSkuId])) {
                continue;
            }
            if (0 > $arrPickupSkusMap[$intSkuId] - $intSkuDistributeAmount) {
                $arrStockouOrderSkuPickupMap[$intStockoutOrderId][$intSkuId] = $arrPickupSkusMap[$intSkuId];
                $arrPickupSkusMap[$intSkuId] = 0;
            } else {
                $arrStockouOrderSkuPickupMap[$intStockoutOrderId][$intSkuId] = $intSkuDistributeAmount;
            }
        }

        foreach ($arrStockouOrderSkuPickupMap as $intStockoutOrderId => $arrStockouOrderSkuPickupInfo) {
            $arrStockouOrderSkuPickupItem['stockout_order_id'] = $intStockoutOrderId;
            foreach ($arrStockouOrderSkuPickupInfo as $intSkuId => $intSkuPickupAmount) {
                $arrStockouOrderSkuPickupItem['pickup_skus'][] = [
                    'sku_id' => $intSkuId,
                    'pickup_amount' => $intSkuPickupAmount,
                ];
            }
            $arrStockouOrderSkuPickupList[] = $arrStockouOrderSkuPickupItem;
        }
        return $arrStockouOrderSkuPickupList;
    }

}

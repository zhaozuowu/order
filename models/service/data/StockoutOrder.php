<?php

/**
 * @name Service_Data_StockoutOrder
 * @desc 出库订单操作类
 * @author zhaozuowu@iwaimai.baidu.com　
 */
class Service_Data_StockoutOrder
{
    /**
     * 出库单状态列表
     */
    const INIT_STOCKOUT_ORDER_STATUS = 10;//待审核
    const STAY_PICKING_STOCKOUT_ORDER_STATUS = 20;//待拣货
    const STAY_RECEIVED_STOCKOUT_ORDER_STATUS = 25;//待揽收
    const STOCKOUTED_STOCKOUT_ORDER_STATUS = 30;//已出库
    const RECEIVED_STOCKOUT_ORDER_STATUS = 40;//已签收
    const AUDIT_NOT_PASSED_STOCKOUT_ORDER_STATUS = 50;//审核不通过
    /**
     * 出库单状态列表
     * @var array
     */
    protected $stockoutOrderStatusList = [
        '10' => '待审核',
        '20' => '待拣货',
        '25' => '待揽收',
        '30' => '已出库',
        '40' => '已签收',
        '50' => '审核不通过',
    ];
    /**
     * orm obj
     * @var Model_Orm_StockoutOrder
     */
    protected $objOrmStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();

    }


    /**
     * 获取下一步操作的出库单操作状态
     * @param $stockoutOrderStatus 出库单号
     * @return bool
     */
    public function getNextStockoutOrderStatus($stockoutOrderStatus)
    {
        $stockoutOrderList = $this->stockoutOrderStatusList;
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

        if (empty($strStockoutOrderId)) {
            Bd_Log::warning(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息

        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }

        $stayRecevied = Service_Data_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
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
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            $objStockoutOrder = new Model_Orm_StockoutOrder();
            $objOrmStockoutOrder->create($arrCreateParams, false);
            $this->createStockoutOrderSku($arrInput['skus']);
        });
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
        if (!empty($arrInput['stockout_order_type'])) {
            $arrCreateParams['stockout_order_type'] = intval($arrInput['stockout_order_type']);
        }
        if (!empty($arrInput['warehouse_name'])) {
            $arrCreateParams['warehouse_name'] = intval($arrInput['warehouse_name']);
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
}
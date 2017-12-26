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
     * 根据出库单号获取出库单信息
     * @param $stockoutOrderId 出库单号
     * @return array
     */
    public function getStockoutOrderInfoById($stockoutOrderId)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return [];
        }

        $condition = ['stockout_order_id' => $stockoutOrderId];
        $stockoutOrderInfo = $this->objOrmStockoutOrder->findOne($condition);
        if (empty($stockoutOrderInfo)) {
            return [];
        }
        $stockoutOrderInfo = $stockoutOrderInfo->toArray();

        Bd_Log::debug(__METHOD__ . ' return: ' . json_encode($stockoutOrderInfo));
        return $stockoutOrderInfo;


    }

    /**
     * 根据出库单号更新出库单状态
     * @param $stockoutOrderId
     * @param $updateData
     * @return bool|int|mysqli|null
     */
    public function updateStockoutOrderStatusById($stockoutOrderId, $updateData)
    {
        Bd_Log::debug(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
        $stockoutOrderId = empty($stockoutOrderId) ? 0 : intval($stockoutOrderId);
        if (empty($stockoutOrderId)) {
            return false;
        }
        $condition = ['stockout_order_id' => $stockoutOrderId];
        $stockoutOrderInfo = $this->objOrmStockoutOrder->findOne($condition);
        if (empty($stockoutOrderInfo)) {
            return false;
        }
        $res = $stockoutOrderInfo->update($updateData);
        return $res;
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
     * 根据出库单号，完成揽收
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function deliveryOrder($arrInput)
    {
        $stockoutOrderId = isset($arrInput['stockout_order_id']) ? intval($arrInput['stockout_order_id']) : 0;

        if (empty($stockoutOrderId)) {
            Bd_Log::warning(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->getStockoutOrderInfoById($stockoutOrderId);//获取出库订单信息

        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $stockoutOrderId . 'no data');
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
        $result = $this->updateStockoutOrderStatusById($stockoutOrderId, $updateData);
        if (empty($result)) {
            Bd_Log::warning(__METHOD__ . ' update stockout_order_status fail  become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        return [];
    }
}
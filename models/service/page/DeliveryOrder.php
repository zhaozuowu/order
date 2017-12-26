<?php

/**
 * @name Service_Page_DeliveryOrder
 * @desc TMS完成揽收
 * @author nscm
 */
class Service_Page_DeliveryOrder
{
    /**
     * @var Service_Data_StockoutOrder
     */
    protected $objStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockoutOrder = new Service_Data_StockoutOrder();
    }

    /**
     * execute
     */
    public function execute($arrInput)
    {
        $stockoutOrderId = isset($arrInput['stockout_order_id']) ? intval($arrInput['stockout_order_id']) : 0;

        if (empty($stockoutOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objStockoutOrder->getStockoutOrderInfoById($stockoutOrderId);//获取出库订单信息

        if (empty($stockoutOrderInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }
        $stayRecevied = Service_Data_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
        if ($stockoutOrderInfo['stockout_order_status'] != $stayRecevied) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);

        }

        $nextStockoutOrderStatus = $this->objStockoutOrder->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);//获取下一步操作状态
        if (empty($nextStockoutOrderStatus)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        $updateData = ['stockout_order_status' => $nextStockoutOrderStatus];
        $result = $this->objStockoutOrder->updateStockoutOrderStatusById($stockoutOrderId, $updateData);
        if (empty($result)) {
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        return [];

    }
}
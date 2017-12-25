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
            throw  new Exception('参数有误', 1000);
        }

        $stockoutOrderInfo = $this->objStockoutOrder->getStockoutOrderInfoById($stockoutOrderId);

        if (empty($stockoutOrderInfo)) {
            throw  new Exception('订单不存在', 2000);
        }

        if ($stockoutOrderInfo['stockout_order_status'] != 20) {
            throw  new Exception('订单状态有误', 2000);
        }

        $result = $this->objStockoutOrder->updateStockoutOrderStatusById($stockoutOrderId);

        if (empty($result)) {
            throw  new Exception('更新失败', 3000);
        }

    }
}
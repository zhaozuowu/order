<?php

/**
 * @name Controller_StockoutThriftService
 * @desc 创建出库单
 * @author  jinyu02@iwaimai.baidu.com
 */
class Controller_StockoutThriftService
{

    /**
     * @var
     */
    protected $objData;

    /**
     * init
     */
    public function __construct()
    {
        $this->objDeliveryOrderService = new Service_Page_Stockout_DeliveryOrder();
        $this->objFinishOrderService = new Service_Page_Stockout_FinishOrder();
        $this->objGetCancelStatus = new Service_Page_Stockout_GetCancelStatus();
    }


    /**
     * TMS完成揽收
     * @param $stockoutOrderId
     * @return array
     */
    public function deliveryOrder($stockoutOrderId)
    {
        $arrInputId = ['stockout_order_id' => $stockoutOrderId];
        return $this->objDeliveryOrderService->execute($stockoutOrderId);
    }


    /**
     * TMS完成门店签收
     * @param $arrInput
     * @return array
     */
    public function finishOrder($arrInput)
    {
        $strStockoutOrderId = isset($arrInput['stockout_order_id']) ? $arrInput['stockout_order_id'] : '';
        $signupStatus = isset($arrInput['signup_status']) ? intval($arrInput['signup_status']) : 0;
        $signupUpcs = isset($arrInput['signup_upcs']) ? json_decode($arrInput['signup_upcs'], true) : [];
        $arrInput = [
            'stockout_order_id' => $strStockoutOrderId,
            'signup_status' => $signupStatus,
            'signup_upcs' => $signupUpcs,
        ];
        return $this->objFinishOrderService->execute($arrInput);
    }

    /**
     * 获取出库单取消状态
     * @param string $strStockoutOrderId
     * @return integer
     */
    public function getCancelStatus($strStockoutOrderId) {
        return $this->objGetCancelStatus->execute($strStockoutOrderId);
    }
}

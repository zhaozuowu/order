<?php

/**
 * @name Controller_StockoutService
 * @desc 创建出库单
 * @author  jinyu02@iwaimai.baidu.com
 */
class Controller_StockoutService
{

    /**
     * @var
     */
    protected $objData;

    /**
     * @var Service_Page_Stockout_DeliveryOrder
     */
    protected $objDeliveryOrderService;

    /**
     * @var Service_Page_Stockout_FinishOrder
     */
    protected $objFinishOrderService;

    /**
     * @var Service_Page_Stockout_GetCancelStatus
     */
    protected $objGetCancelStatus;

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
        $intSignupStatus = isset($arrInput['signup_status']) ? intval($arrInput['signup_status']) : 0;
        $arrSignupSkus = isset($arrInput['signup_skus']) ? json_decode($arrInput['signup_skus'], true) : [];
        $arrInput = [
            'stockout_order_id' => $strStockoutOrderId,
            'signup_status' => $intSignupStatus,
            'signup_skus' => $arrSignupSkus,
        ];
        return $this->objFinishOrderService->execute($arrInput);
    }

    /**
     * 获取出库单取消状态
     * @param array $arrInput
     * @return mixed
     */
    public function getCancelStatus($arrInput) {
        return $this->objGetCancelStatus->execute($arrInput);
    }
}

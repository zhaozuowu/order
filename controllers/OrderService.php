<?php
/**
 * @name Controller_OrderService
 * @desc order service
 * @author  bochao.lv@ele.me
 */
class Controller_OrderService extends Nscm_Base_ControllerService {

    /**
     * 地址映射
     * @var array
     */
    public $arrMap = [
        'Action_Service_CancelStockOutorderService' => 'actions/service/CancelStockOutorderService.php',
        'Action_Service_CreateRemoveSiteStockInOrderService' => 'actions/service/CreateRemoveSiteStockInOrderService.php',
        'Action_Service_CreateReserveOrderService' => 'actions/service/CreateReserveOrderService.php',
        'Action_Service_CreateStockInOrderService' => 'actions/service/CreateStockInOrderService.php',
        'Action_Service_DestroyReserveOrderService' => 'actions/service/DestroyReserveOrderService.php',
        'Action_Service_GetAutoStockoutStockinWaitingSkuService' => 'actions/service/GetAutoStockoutStockinWaitingSkuService.php',
        'Action_Service_GetBusinessFormOrderByIdsService' => 'actions/service/GetBusinessFormOrderByIdsService.php',
        'Action_Service_GetOrderDetailFormService' => 'actions/service/GetOrderDetailFormService.php',
        'Action_Service_GetStockinReserveDetailFormService' => 'actions/service/GetStockinReserveDetailFormService.php',
        'Action_Service_GetStockinStockoutOrderInfoListService' => 'actions/service/GetStockinStockoutOrderInfoListService.php',
        'Action_Service_GetStockinStockoutOrderInfoService' => 'actions/service/GetStockinStockoutOrderInfoService.php',
        'Action_Service_GetStockoutByIdsService' => 'actions/service/GetStockoutByIdsService.php',
        'Action_Service_GetStockoutDetailFormService' => 'actions/service/GetStockoutDetailFormService.php',
        'Action_Service_GetStockoutStockinDetailFormService' => 'actions/service/GetStockoutStockinDetailFormService.php',
        'Action_Service_PreCancelStockOutOrderService' => 'actions/service/PreCancelStockOutOrderService.php',
        'Action_Service_RollbackCancelStockOutOrderService' => 'actions/service/RollbackCancelStockOutOrderService.php',
    ];

    public function CancelStockoutOrderService($arrRequest) {
        $objAction = new Action_Service_CancelStockOutorderService($arrRequest);
        return $objAction->execute();
    }
    public function CreateRemoveSiteStockInOrderService($arrRequest) {
        $objAction = new Action_Service_CreateRemoveSiteStockInOrderService($arrRequest);
        return $objAction->execute();
    }
    public function CreateReserveOrderService($arrRequest) {
        $objAction = new Action_Service_CreateReserveOrderService($arrRequest);
        return $objAction->execute();
    }
    public function CreateStockInOrderService($arrRequest) {
        $objAction = new Action_Service_CreateStockInOrderService($arrRequest);
        return $objAction->execute();
    }
    public function DestroyReserveOrderService($arrRequest) {
        $objAction = new Action_Service_DestroyReserveOrderService($arrRequest);
        return $objAction->execute();
    }
    public function GetAutoStockoutStockinWaitingSkusService($arrRequest) {
        $objAction = new Action_Service_GetAutoStockoutStockinWaitingSkuService($arrRequest);
        return $objAction->execute();
    }
    public function GetBusinessFormOrderByIdsService($arrRequest) {
        $objAction = new Action_Service_GetBusinessFormOrderByIdsService($arrRequest);
        return $objAction->execute();
    }
    public function GetOrderDetailFormService($arrRequest) {
        $objAction = new Action_Service_GetOrderDetailFormService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockinReserveDetailFormService($arrRequest) {
        $objAction = new Action_Service_GetStockinReserveDetailFormService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockinStockoutOrderInfoListService($arrRequest) {
        $objAction = new Action_Service_GetStockinStockoutOrderInfoListService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockinStockoutOrderInfoService($arrRequest) {
        $objAction = new Action_Service_GetStockinStockoutOrderInfoService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockoutByIdsService($arrRequest) {
        $objAction = new Action_Service_GetStockoutByIdsService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockoutDetailFormService($arrRequest) {
        $objAction = new Action_Service_GetStockoutDetailFormService($arrRequest);
        return $objAction->execute();
    }
    public function GetStockoutStockinDetailFormService($arrRequest) {
        $objAction = new Action_Service_GetStockoutStockinDetailFormService($arrRequest);
        return $objAction->execute();
    }
    public function PreCancelStockOutOrderService($arrRequest) {
        $objAction = new Action_Service_PreCancelStockOutOrderService($arrRequest);
        return $objAction->execute();
    }
    public function RollbackCancelStockOutOrderService($arrRequest) {
        $objAction = new Action_Service_RollbackCancelStockOutOrderService($arrRequest);
        return $objAction->execute();
    }


}

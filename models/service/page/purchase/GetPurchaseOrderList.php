<?php

/**
 * @name Service_Page_Purchase_GetPurchaseOrderList
 * @desc sample page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */
class Service_Page_Purchase_GetPurchaseOrderList implements Order_Base_Page
{
    /**
     *
     * @var Service_Data_PurchaseOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Purchase_GetPurchaseOrderList constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_PurchaseOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $purchaseOrderStatus = $arrInput['purchase_order_status'];
        $arrWarehouseId = $arrInput['warehouse_id'];
        $purchaseOrderId = $arrInput['purchase_order_id'];
        $vendorId = $arrInput['vendor_id'];
        $createTime = [
            'start' => $arrInput['create_time_start'],
            'end' => $arrInput['create_time_end'],
        ];

        $OrderPlanTime = [
            'start' => $arrInput['purchase_order_plan_time_start'],
            'end' => $arrInput['purchase_order_plan_time_end'],
        ];

        $stockinTime = [
            'start' => $arrInput['stockin_time_start'],
            'end' => $arrInput['stockin_time_end'],
        ];

        $pageNum = $arrInput['page_num'];
        $pageSize = $arrInput['page_size'];

        return $this->objServiceData->getPurchaseOrderList($purchaseOrderStatus,
            $arrWarehouseId,
            $purchaseOrderId,
            $vendorId,
            $createTime,
            $OrderPlanTime,
            $stockinTime,
            $pageNum,
            $pageSize);
    }
}

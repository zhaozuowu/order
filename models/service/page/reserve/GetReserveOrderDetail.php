<?php

/**
 * @name Service_Page_Reserve_GetReserveOrderDetail
 * @desc get reserve order detail page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Reserve_GetReserveOrderDetail implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_ReserveOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Reserve_GetReserveOrderDetail constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strPurchaseOrderStatus = $arrInput['purchase_order_status'];
        $strWarehouseId = $arrInput['warehouse_id'];
        $strPurchaseOrderId = $arrInput['purchase_order_id'];
        $intVendorId = $arrInput['vendor_id'];
        $arrCreateTime = [
            'start' => $arrInput['create_time_start'],
            'end' => $arrInput['create_time_end'],
        ];

        $arrOrderPlanTime = [
            'start' => $arrInput['purchase_order_plan_time_start'],
            'end' => $arrInput['purchase_order_plan_time_end'],
        ];

        $arrStockinTime = [
            'start' => $arrInput['stockin_time_start'],
            'end' => $arrInput['stockin_time_end'],
        ];

        $intPageNum = $arrInput['page_num'];
        $intPageSize = $arrInput['page_size'];

        return $this->objServiceData->getReserveOrderList([],
            $strPurchaseOrderStatus,
            $strWarehouseId,
            $strPurchaseOrderId,
            $intVendorId,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }
}

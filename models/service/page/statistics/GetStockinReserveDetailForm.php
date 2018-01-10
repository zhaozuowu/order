<?php

/**
 * @name Service_Page_Statistics_GetStockinReserveDetailForm
 * @desc 报表-获取采购入库明细（分页），page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Statistics_GetStockinReserveDetailForm implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_ReserveOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Statistics_GetStockinReserveDetailForm constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Statistics_GetStockinReserveDetailForm();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strWarehouseId = $arrInput['warehouse_id'];
        $strStockinOrderId = $arrInput['stockin_order_id'];
        $strSourceOrderId = $arrInput['source_order_id'];
        $intSkuId = $arrInput['sku_id'];
        $intVendorId = $arrInput['vendor_id'];

        $arrOrderPlanTime = [
            'start' => $arrInput['reserve_order_plan_time_start'],
            'end' => $arrInput['reserve_order_plan_time_end'],
        ];

        $arrStockinTime = [
            'start' => $arrInput['stockin_time_start'],
            'end' => $arrInput['stockin_time_end'],
        ];

        $intPageNum = $arrInput['page_num'];
        $intPageSize = $arrInput['page_size'];

        return $this->objServiceData->getStockinReserveDetailForm(
            $strWarehouseId,
            $strStockinOrderId,
            $strSourceOrderId,
            $intSkuId,
            $intVendorId,
            $arrOrderPlanTime,
            $arrStockinTime,
            $intPageNum,
            $intPageSize);
    }
}

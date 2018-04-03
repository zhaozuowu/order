<?php

/**
 * @name Service_Page_Stockin_GetStockinOrderList
 * @desc 获取入库单列表（分页）page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Stockin_GetStockinOrderList implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     * 获取入库单列表（分页）
     *
     * @var Service_Data_StockinOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Stockin_GetStockinOrderList constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * @param array $arrInput
     * @return array|mixed
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function execute($arrInput)
    {
        $strStockinOrderType = $arrInput['stockin_order_type'];
        $intDataSource = intval($arrInput['data_source']);
        $strStockinOrderId = $arrInput['stockin_order_id'];
        $intStockinOrderSourceType = intval($arrInput['stockin_order_source_type']);
        $intStockinOrderStatus = intval($arrInput['stockin_order_status']);
        $strWarehouseId = $arrInput['warehouse_ids'];
        $intSourceSupplierId = $arrInput['source_supplier_id'];
        $strSourceOrderId = $arrInput['source_order_id'];
        $arrCreateTime = [
            'start' => $arrInput['create_time_start'],
            'end' => $arrInput['create_time_end'],
        ];
        $arrOrderPlanTime = [
            'start' => $arrInput['stockin_order_plan_time_start'],
            'end' => $arrInput['stockin_order_plan_time_end'],
        ];
        $arrStockinTime = [
            'start' => $arrInput['stockin_time_start'],
            'end' => $arrInput['stockin_time_end'],
        ];
        $arrStockinDestoryTime = [
            'start' => $arrInput['stockin_destory_time_start'],
            'end' => $arrInput['stockin_destory_time_end'],
        ];
        $intPageNum = $arrInput['page_num'];
        $intPageSize = $arrInput['page_size'];
        return $this->objServiceData->getStockinOrderList(
            $strStockinOrderType,
            $intDataSource,
            $strStockinOrderId,
            $intStockinOrderSourceType,
            $intStockinOrderStatus,
            $strWarehouseId,
            $intSourceSupplierId,
            $strSourceOrderId,
            $arrCreateTime,
            $arrOrderPlanTime,
            $arrStockinTime,
            $arrStockinDestoryTime,
            $intPageNum,
            $intPageSize);
    }
}

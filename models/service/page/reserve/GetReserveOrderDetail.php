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
        $strReserveOrderId = strval($arrInput['reserve_order_id']);
        $ret = $this->objServiceData->getReserveOrderInfoByOrderId($strReserveOrderId);

        if(empty($ret)){
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_QUERY_RESULT_EMPTY);
        }

        $intCurrentUserId = $arrInput['_session']['user_id'];
        $ret['display_operate_tip'] = false;
        $arrRetLastRecord = $ret['last_operate_record'];
        if ((!empty($arrRetLastRecord))
            && ($intCurrentUserId != $arrRetLastRecord['operate_user_id'])) {
            $ret['display_operate_tip'] = true;
            $ret['last_operate_time'] = $arrRetLastRecord['operate_time'];
            $ret['last_operate_name'] = $arrRetLastRecord['operate_user_name'];
            $ret['last_operate_device'] = $arrRetLastRecord['operate_device'];
        }

        // 如果传入了warehouse_id字段，则进行字段校验该单仓库是不是给定的仓库
        $strWarehouseId = $arrInput['warehouse_id'];
        if (!empty($strWarehouseId)) {
            if ($strWarehouseId != $ret['warehouse_id']) {
                Order_BusinessError::throwException(Order_Error_Code::ORDER_NOT_IN_GIVEN_WAREHOUSE);
            }
        }

        return $ret;
    }
}

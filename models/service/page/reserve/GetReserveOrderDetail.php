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
        $ret = $this->objServiceData->getReserveOrderInfoByReserveOrderId($strReserveOrderId);

        if(empty($ret)){
            if (true == Order_Util::isReserveOrderId($strReserveOrderId)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_RESERVE_ORDER_NOT_EXIST);
            } else if (true == Order_Util::isPurchaseOrderId($strReserveOrderId)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_PURCHASE_ORDER_NOT_EXIST);
            }
        }

        // TODO: 此处的逻辑还需要再次检查

        $intCurrentUserId = $arrInput['_session']['user_id'];
        $arrRet['display_operate_tip'] = false;
        $arrRetLastRecord = $ret['last_operate_record'];
        if ((!empty($arrRetLastRecord))
            && ($intCurrentUserId != $arrRetLastRecord['operate_user_id'])) {
            $arrRet['display_operate_tip'] = true;
            $arrRet['last_operate_time'] = $arrRetLastRecord['operate_time'];
            $arrRet['last_operate_name'] = $arrRetLastRecord['operate_user_name'];
            $arrRet['last_operate_device'] = $arrRetLastRecord['operate_device'];
        }

        return $ret;
    }
}

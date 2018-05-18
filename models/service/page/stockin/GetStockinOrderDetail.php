<?php

/**
 * @name Service_Page_Stockin_GetStockinOrderDetail
 * @desc 获取采购单详情，get reserve order detail page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Stockin_GetStockinOrderDetail implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_StockinOrder
     */
    private $objServiceData;

    /**
     * Service_Page_Reserve_GetReserveOrderDetail constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * @param array $arrInput
     * @return array|mixed
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strStockinOrderId = strval($arrInput['stockin_order_id']);
        $ret = $this->objServiceData->getStockinOrderInfoByStockinOrderId($strStockinOrderId);
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

        return $ret;
    }
}

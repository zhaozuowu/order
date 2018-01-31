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
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_RESERVE_ORDER_NOT_EXIST);
        }

        return $ret;
    }
}

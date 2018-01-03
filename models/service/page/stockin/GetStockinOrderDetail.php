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
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strStockinOrderId = strval($arrInput['stockin_order_id']);

        return $this->objServiceData->getStockinOrderInfoByStockinOrderId($strStockinOrderId);
    }
}

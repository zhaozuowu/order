<?php

/**
 * @name Service_Page_Reserve_GetReserveOrderStatistics
 * @desc get reserve order list page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */
class Service_Page_Reserve_GetReserveOrderStatistics implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_ReserveOrder
     */
    private $objServiceData;

    /**
     * constructor
     *
     * Service_Page_Reserve_GetReserveOrderStatistics constructor.
     */
    public function __construct()
    {
        $this->objServiceData = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * 获取采购单状态统计
     *
     * @param array $arrInput = []
     * @return array
     */
    public function execute($arrInput)
    {
        return $this->objServiceData->getReserveOrderStatistics();
    }
}

<?php

/**
 * @name Service_Page_Reserve_GetReserveOrderSkuList
 * @desc page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Reserve_GetReserveOrderSkuList implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_GetReserveOrderSkuList 数据对象
     */
    private $objServiceData;

    /**
     * Service_Page_Reserve_GetReserveOrderSkuList constructor.
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
        $strReserveOrderId = $arrInput['reserve_order_id'];

        $intPageNum = $arrInput['page_num'];
        $intPageSize = $arrInput['page_size'];

        return $this->objServiceData->getReserveOrderSkuList(
            $strReserveOrderId,
            $intPageNum,
            $intPageSize);
    }
}

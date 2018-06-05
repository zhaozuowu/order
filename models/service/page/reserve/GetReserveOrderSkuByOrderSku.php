<?php

/**
 * @name Service_Page_Reserve_GetReserveOrderSkuByOrderSku
 * @desc 根据预约单号和商品编码/条码查询商品信息
 * @author nscm
 */

class Service_Page_Reserve_GetReserveOrderSkuByOrderSku implements Order_Base_Page
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
        $strSkuUpcId = strval($arrInput['sku_upc_id']);
        $ret = $this->objServiceData->getReserveOrderSkuInfo($strReserveOrderId, $strSkuUpcId);

        return $ret;
    }
}

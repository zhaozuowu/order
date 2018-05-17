<?php

/**
 * @name Service_Page_Stockin_GetStockinSkuByOrderSku
 * @desc 根据入库单号和商品编码/条码查询商品信息
 * @author nscm
 */

class Service_Page_Stockin_GetStockinSkuByOrderSku implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_Stockin
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
        $strSkuUpcId = strval($arrInput['sku_upc_id']);
        $ret = $this->objServiceData->getStockinOrderSkuInfo($strStockinOrderId, $strSkuUpcId);

        return $ret;
    }
}

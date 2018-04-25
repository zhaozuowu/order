<?php

/**
 * @name Service_Page_Stockin_GetStockinOrderSkus
 * @desc 获取入库单商品列表（不分页），page service, 和action对应，组织页面逻辑，组合调用data service
 * @author nscm
 */

class Service_Page_Stockin_GetStockinOrderSkus implements Order_Base_Page
{
    /**
     * Page Data服务对象，进行数据校验和处理
     *
     * @var Service_Data_Stockin_StockinOrder 数据对象
     */
    private $objServiceData;

    /**
     * Service_Page_Stockin_GetStockinOrderSkus constructor.
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
        $strStockinOrderId = $arrInput['stockin_order_id'];
        
        return $this->objServiceData->getStockinOrderSkus($strStockinOrderId);
    }
}

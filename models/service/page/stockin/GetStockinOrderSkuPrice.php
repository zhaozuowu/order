<?php
/**
 * @name Service_Page_Stockin_GetStockinOrderSkuPrice
 * @desc Service_Page_Stockin_GetStockinOrderSkuPrice
 * @author hang.song02@ele.me
 */
class Service_Page_Stockin_GetStockinOrderSkuPrice implements Order_Base_Page
{
    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    private $objDataStockin;

    /**
     * Service_Page_Stockin_CreateStockinOrder constructor.
     */
    function __construct()
    {
        $this->objDataStockin = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        return $this->objDataStockin->getStockinOrderSkuInfo($arrInput['order_id'], $arrInput['sku_id']);
    }
}
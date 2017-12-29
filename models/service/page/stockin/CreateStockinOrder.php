<?php
/**
 * @name Service_Page_Stockin_CreateStockinOrder
 * @desc Service_Page_Stockin_CreateStockinOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Stockin_CreateStockinOrder implements Order_Base_Page
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
     */
    public function execute($arrInput)
    {

    }
}
<?php
/**
 * @name Service_Page_Stockin_ConfirmStockinOrder
 * @desc  确认销退入库单
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Stockin_ConfirmStockinOrder implements Order_Base_Page
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
     * execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function execute($arrInput)
    {
        $this->objDataStockin->confirmStockInOrder($arrInput['stockin_order_id'], $arrInput['sku_info_list']);
        return [];
    }
}
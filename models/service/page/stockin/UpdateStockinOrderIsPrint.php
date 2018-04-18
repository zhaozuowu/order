<?php
/**
 * @name Service_Page_Stockin_UpdateStockinOrderIsPrint
 * @desc Service_Page_Stockin_UpdateStockinOrderIsPrint
 * @author huabang.xue@ele.me
 */
class Service_Page_Stockin_UpdateStockinOrderIsPrint implements Order_Base_Page
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
     * @return int
     * @throws Order_BusinessError
     * @throws Wm_Orm_Error
     * @throws Exception
     */
    public function execute($arrInput)
    {
        $arrStockinOrderIds = explode(',', $arrInput['stockin_order_ids']);
        return $this->objDataStockin->updateStockinOrderIsPrint($arrStockinOrderIds);
    }
}
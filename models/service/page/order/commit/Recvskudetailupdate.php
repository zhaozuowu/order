<?php
/**
 * @name Service_Page_Order_Commit_RecvSkuDetailUpdate
 * @desc Service_Page_Order_Commit_RecvSkuDetailUpdate
 * @author bochao.lv@ele.me
 */
class Service_Page_Order_Commit_RecvSkuDetailUpdate extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_StockoutOrder
     */
    private $objDataStockoutOrder;

    /**
     *
     */
    public function beforeExecute()
    {
        $this->objDataStockoutOrder = new Service_Data_StockoutOrder();
    }

    /**
     * @param array $arrRequest
     * @return []
     * @throws Exception
     * @throws Wm_Orm_Error
     */
    public function myExecute($arrRequest)
    {
        $intStockoutOrderId = intval($arrRequest['stockout_order_id']);
        $arrStockoutSkuInfo = $arrRequest['sku_info'];
        $this->objDataStockoutOrder->recvSkuDetail($intStockoutOrderId, $arrStockoutSkuInfo);
        return [];
    }
}
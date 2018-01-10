<?php
/**
 * @name Service_Page_Order_Commit_ReserveOrderCreate
 * @desc Service_Page_Order_Commit_ReserveOrderCreate
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_ReserveOrderCreate extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataPurchase;

    /**
     *
     */
    public function beforeExecute()
    {
        $this->objDataPurchase = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrRequest
     * @return []
     * @throws Exception
     * @throws Wm_Orm_Error
     */
    public function myExecute($arrRequest)
    {
        $intPurchaseOrderId = intval($arrRequest['purchase_order_id']);
        $this->objDataPurchase->createReserveOrderByPurchaseOrderId($intPurchaseOrderId);
        return [];
    }
}
<?php
/**
 * @name Service_Page_Order_Commit_Reserveorderdestroy
 * @desc Service_Page_Order_Commit_Reserveorderdestroy
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Reserveorderdestroy extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataReserve;

    /**
     * Service_Page_Purchase_CreatePurchaseOrder constructor.
     */
    public function beforeExecute()
    {
        $this->objDataReserve = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * do execute
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function myExecute($arrInput)
    {
        $intNscmPurchaseOrderId = intval($arrInput['purchase_order_id']);
        $intDestroyType = intval($arrInput['destroy_type']);
        $this->objDataReserve->destroyReserveOrder($intNscmPurchaseOrderId, $intDestroyType);
        return [];
    }
}
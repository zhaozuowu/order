<?php
/**
 * @name Service_Page_Order_Commit_Nscmpurchaseordersync
 * @desc 订单统计操作
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Nscmpurchaseordersync extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_Reserve_ReserveOrder $objData
     */
    private $objData;

    /**
     * constructor
     */
    public function beforeExecute()
    {
        parent::beforeExecute();
        $this->objData = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * execute
     * @param $arrRequest
     * @throws Nscm_Exception_Error
     */
    public function myExecute($arrRequest)
    {
        Bd_Log::debug(__METHOD__ . ' request params: ' . json_encode($arrRequest));
        $intInboundId = $arrRequest['inbound_id'];
        $intStatus = $arrRequest['status'];
        $intActualTime = $arrRequest['actual_time'];
        $arrItems = $arrRequest['items'];
        $this->objData->syncInboundDirect($intInboundId, $intStatus, $intActualTime, $arrItems);
    }
}
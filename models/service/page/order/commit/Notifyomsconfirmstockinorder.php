<?php
/**
 * @name Service_Page_Order_Commit_Notifyomsconfirmstockinorder
 * @desc 异步通知OMS确认销退入库单结果
 * @author huabang.xue@ele.me
 */
class Service_Page_Order_Commit_Notifyomsconfirmstockinorder extends Wm_Lib_Wmq_CommitPageService {
    
    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    protected $objDsStockinOrder;

    /**
     * init
     */
    public function __construct() {
        $this->objDsStockinOrder = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * create stockout order
     * @param array $arrInput
     * @return bool
     * @throws Order_BusinessError
     */
    public function myExecute($arrInput) {
        Bd_Log::trace(sprintf("method[%s] arrInput[%s]", __METHOD__, json_encode($arrInput)));
        $boolResult = $this->objDsStockinOrder->asynchronousNotifyOmsConfirmStockinResult($arrInput);
        return $boolResult;
    }
}
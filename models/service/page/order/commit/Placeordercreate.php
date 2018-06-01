<?php
/**
 * @name Placeordercreate.php
 * @desc Placeordercreate.php
 * @author yu.jin03@ele.me
 */

class Service_Page_Order_Commit_Placeordercreate extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_PlaceOrder
     */
    protected $objDsPlaceOrder;

    /**
     * Service_Page_Order_Commit_Placeordercreate constructor.
     */
    public function __construct()
    {
        $this->objDsPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * @param $arrRequest
     * @return array
     * @throws Wm_Error
     */
    public function myExecute($arrRequest)
    {
        Bd_Log::trace(sprintf("method[%s] arrRequest[%s]", __METHOD__, json_encode($arrRequest)));
        $arrStockinOrderIds = explode(',', $arrRequest['stockin_order_ids']);
        return $this->objDsPlaceOrder->createPlaceOrder($arrStockinOrderIds);
    }
}
<?php
/**
 * @name Service_Page_Pickup_FinishPickupOrder
 * @desc 完成拣货
 * @author hang.song02@ele.me
 */

class Service_Page_Pickup_FinishPickupOrder
{
    /**
    * pick up order data service
    * @var Service_Data_Sku
    */
    protected $objPickupOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objPickupOrder = new Service_Data_PickupOrder();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return int
     * @throws Order_BusinessError
     * @throws Exception
     */
    public function execute($arrInput)
    {
        $intPickupOrderId = intval($arrInput['pickup_order_id']);
        $strRemark = $arrInput['remark'];
        $arrPickupSkus = is_array($arrInput['pickup_skus']) ? $arrInput['pickup_skus'] : json_decode($arrInput['pickup_skus'], true);
        $userId = !empty($arrInput['_session']['user_id']) ? $arrInput['_session']['user_id']:0;
        $userName = !empty($arrInput['_session']['user_name']) ? $arrInput['_session']['user_name']:'' ;
        Bd_Log::debug("finishPickupOrder execute userinfo:".json_encode($arrInput));
        $ret = $this->objPickupOrder->finishPickupOrder($intPickupOrderId, $arrPickupSkus, $userId, $userName, $strRemark);
        return $ret;
    }
}
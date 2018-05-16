<?php
/**
 * @name Service_Page_Main_SaveOrderOperateRecord
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/15 21:34
 */

class Service_Page_Main_SaveOrderOperateRecord implements Order_Base_Page
{
    /**
     * @var Service_Data_Stockin_StockinOrder
     */
    private $data;

    /**
     * Service_Page_Main_SaveOrderOperateRecord constructor.
     */
    function __construct()
    {
        $this->data = new Service_Data_Stockin_StockinOrder();
    }

    /**
     * function execute
     * @param array $arrInput
     * @return bool
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strOrderId = $arrInput['order_id'];
        $strDevice = $arrInput['device'];
        $strUsername = $arrInput['_session']['user_name'];
        $intUserId = $arrInput['_session']['user_id'];
        $ret = $this->data->addOrderOperateRecord($strOrderId, $strUsername, $intUserId, $strDevice);
        return $ret;
    }
}
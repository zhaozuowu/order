<?php
/**
 * Class Service_Page_Shift_CancelOrder
 */

class Service_Page_Shift_CancelOrder
{
    /**
     * @var Service_Data_ShiftOrder
     */
    protected $objShiftOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objShiftOrder = new Service_Data_ShiftOrder();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        // 去掉前缀
        if(!empty($arrInput['shift_order_id'])) {
            $arrInput['shift_order_id'] =
                intval(Order_Util::trimShiftOrderIdPrefix($arrInput['shift_order_id']));
        }
        // 取消移位单
        return $this->objShiftOrder->cancelShiftOrder($arrInput);
    }
}

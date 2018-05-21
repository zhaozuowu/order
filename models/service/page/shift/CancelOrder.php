<?php
/**
 * @name Service_Page_Adjust_CreateOrder
 * @desc 新建调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Shift_CancelOrder
{
    /**
     * @var Service_Data_StockAdjustOrder
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
        // 去掉SHO前缀
        if(!empty($arrInput['shift_order_id'])) {
            $arrInput['shift_order_id'] = intval(Order_Util::trimShiftOrderIdPrefix($arrInput['shift_order_id']));
        }else return [];
        // 取消移位单
        $arrOutput = $this->objShiftOrder->cancelShiftOrder($arrInput);
        return $arrOutput;
    }
}

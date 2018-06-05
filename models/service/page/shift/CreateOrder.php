<?php
/**
 * Class Service_Page_Shift_CreateOrder
 */

class Service_Page_Shift_CreateOrder
{
    /**
     * @var Service_Data_ShiftOrder
     */
    protected $objShiftOrder;

    /**
     * @var
     */
    protected $objStockAdjustOrderDetail;

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
        // 生成移位单号
        $arrInput['shift_order_id'] = Order_Util_Util::generateShiftOrderId();
        if(empty($arrInput['shift_order_id'])){
            Bd_Log::warning('generate shift order id failed $arrInput:' . $arrInput);
        }

        // 创建移位单
        $arrOutput = $this->objShiftOrder->createShiftOrder($arrInput);
        return $arrOutput;
    }
}

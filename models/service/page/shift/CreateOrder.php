<?php
/**
 * @name Service_Page_Adjust_CreateOrder
 * @desc 新建调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Shift_CreateOrder
{
    /**
     * @var Service_Data_objShiftOrderr
     */
    protected $objShiftOrder;

    /**
     * @var Service_Data_StockAdjustOrderDetail
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
        $arrInput['warehouse_id'] = '1';
        $arrInput['source_location'] = '1';
        $arrInput['target_location'] = '1';
        $arrInput['detail'] = array();
        $arrInput['detail'][]=array(
            'sku_id'=>1,
            'sku_name'=>'2',
            'upc_id'=>1,
            'upc_unit'=>1,
            'upc_unit_num'=>1,
            'production_time'=>1,
            'expiration_time'=>1,
            'shift_amount'=>1,
            'is_defective'=>1,
        );
        // 生成一个移位单号
        $arrInput['shift_order_id'] = Order_Util_Util::generateShiftOrderId();
        Bd_Log::trace('generate shift order id: ' . $arrInput['shift_order_id']);

        // 创建移位单
        $arrOutput = $this->objShiftOrder->createShiftOrder($arrInput);
        return $arrOutput;
    }
}

<?php
/**
 * @name Service_Page_Adjust_FinishOrder
 * @desc 新建调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Shift_FinishOrder
{
    /**
     * @var Service_Data_StockAdjustOrder
     */
    protected $objShiftOrder;

    /**
     * @var Service_Data_StockAdjustOrderDetail
     */
    protected $objShiftOrderDetail;

    /**
     * init
     */
    public function __construct()
    {
        $this->objShiftOrder = new Service_Data_ShiftOrder();
        $this->objShiftOrderDetail = new Service_Data_ShiftOrderDetail();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {

        $arrOrder = $this->objShiftOrder->getByOrderId($arrInput['shift_order_id']);
        $arrOrderDetail = $this->objShiftOrderDetail->get($arrInput);
        if(empty($arrOrder) || empty($arrOrderDetail)) return false;
        $finishInput = array();
        $finishInput['m_order_id'] = $arrInput['shift_order_id'];
        $finishInput['warehouse_id'] = $arrOrder['warehouse_id'];
        $finishInput['origin_location_code'] = $arrOrder['source_location'];
        $finishInput['target_location_code'] = $arrOrder['target_location'];
        foreach ($arrOrderDetail as $value){
            $detailInput = array();
            $detailInput['sku_id']          = $value['sku_id'];
            $detailInput['expiration_time'] = $value['expiration_time'];
            $detailInput['is_defective']    = $value['is_defective'];
            $detailInput['amount']          = $value['amount'];
            $finishInput['batch_detail'][] = $detailInput;
        }
        // 完成移位单
        $arrOutput = $this->objShiftOrder->finishShiftOrder($finishInput);
        return $arrOutput;
    }
}

<?php
/**
 * @name Service_Page_Shift_GetOrder
 * @desc 获取移位单
 * @author songwenkai@iwaimai.baidu.com
 */

class Service_Page_Shift_GetOrder
{
    /**
     * adjust order data service
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
        // 去掉前缀
        if(!empty($arrInput['shift_order_id'])) {
            $arrInput['shift_order_id'] =
                intval(Order_Util::trimShiftOrderIdPrefix($arrInput['shift_order_id']));
        }

        $intCount = $this->objShiftOrder->getCount($arrInput);
        $arrOutput = $this->objShiftOrder->get($arrInput);
        $arrResult = $this->formatResult($arrOutput);
        return array('total' => $intCount, 'shift_order_list' => $arrResult);
    }

    public function formatResult($arrInput){
        $arrResult = array();
        foreach ($arrInput as $value){
            $row['shift_order_id']  = $value['shift_order_id'];
            $row['warehouse_id']    = $value['warehouse_id'];
            $row['source_location'] = $value['source_location'];
            $row['source_roadway']  = $value['source_roadway'];
            $row['source_area']     = $value['source_area'];
            $row['target_location'] = $value['target_location'];
            $row['target_roadway']  = $value['target_roadway'];
            $row['target_area']     = $value['target_area'];
            $row['status']          = $value['status'];
            $row['creator_name']    = $value['creator_name'];
            $row['sku_kinds']       = $value['sku_kinds'];
            $row['sku_amount']      = $value['sku_amount'];
            $row['create_time']     = date('Y-m-d',$value['create_time']);

            $arrResult[] = $row;
        }
        return $arrResult;
    }
}

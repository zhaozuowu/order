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
            unset($value['id']);
            unset($value['version']);
            unset($value['is_delete']);
            $arrResult[] = $value;
        }
        return $arrResult;
    }
}

<?php
/**
 * @name Service_Page_Adjust_GetOrderDetail
 * @desc 查询采购单sku
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Shift_GetOrderDetail
{

    /**
     * stock adjust order detail data service
     * @var Service_Data_StockAdjustOrderDetail
     */
    protected $objShiftOrder;
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
        // 去掉SAO前缀
        if(!empty($arrInput['shift_order_id'])) {
            $arrInput['shift_order_id'] =
                intval(Order_Util::trimShiftOrderIdPrefix($arrInput['shift_order_id']));
        }else return [];

        $arrOrder = $this->objShiftOrder->getByOrderId($arrInput['shift_order_id']);
        if(empty($arrOrder)) {
            return [];
        }

        $intOrderDetailCount = $this->objShiftOrderDetail->getCountWithGroup($arrInput);
        $arrOrderDetail = $this->objShiftOrderDetail->get($arrInput);

        return $this->formatResult($arrOrder, $intOrderDetailCount, $arrOrderDetail);
    }

    /**
     * 格式化输出返回结果
     * @param array $arrOrder
     * @param int $intCount
     * @param array $arrDetail
     * @return array
     */
    public function formatResult($arrOrder = array(), $intCount = 0, $arrDetail = array())
    {
        $arrRet = $arrOrder;
        $arrRet['shift_order_detail'] = array();
        $arrRet['shift_order_detail']['total'] = $intCount;
        $arrRet['shift_order_detail']['detail'] = $arrDetail;

        return $arrRet;
    }
}

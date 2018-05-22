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
        $row['shift_order_id']  = $arrOrder['shift_order_id'];
        $row['warehouse_id']    = $arrOrder['warehouse_id'];
        $row['source_location'] = $arrOrder['source_location'];
        $row['sourc$rowe_roadway']  = $arrOrder['source_roadway'];
        $row['source_area']     = $arrOrder['source_area'];
        $row['target_location'] = $arrOrder['target_location'];
        $row['target_roadway']  = $arrOrder['target_roadway'];
        $row['target_area']     = $arrOrder['target_area'];
        $row['status']          = $arrOrder['status'];
        $row['creator_name']    = $arrOrder['creater_name'];
        $row['sku_kinds']       = $arrOrder['sku_kinds'];
        $row['sku_amount']      = $arrOrder['sku_amount'];
        $row['create_time']     = strtotime(date('Y-m-d',$arrOrder['create_time']));
        foreach ($arrDetail as &$value){
            $value['upc_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$value['upc_unit']];
            $value['sku_effect_type_text'] = Nscm_Define_Sku::SKU_EFFECT_TYPE_TEXT[$value['sku_effect_type']];
            $value['is_defective_text'] = Nscm_Define_Stock::QUALITY_TEXT_MAP[$value['is_defective']];
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $value['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['production_time']));
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $value['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['expiration_time']));
            }
        }
        $arrRet = $row;
        $arrRet['total'] = $intCount;
        $arrRet['shift_order_detail'] = array();
        $arrRet['shift_order_detail'] = $arrDetail;

        return $arrRet;
    }
}

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
    protected $objSku;

    /**
     * init
     */
    public function __construct()
    {
        $this->objShiftOrder = new Service_Data_ShiftOrder();
        $this->objShiftOrderDetail = new Service_Data_ShiftOrderDetail();
        $this->objSku = new Dao_Ral_Sku();
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
        $skuIds = array_column($arrOrderDetail,'sku_id');
        $skuInfos = $this->objSku->getSkuInfos($skuIds);

        return $this->formatResult($arrOrder, $intOrderDetailCount, $arrOrderDetail,$skuInfos);
    }

    /**
     * 格式化输出返回结果
     * @param array $arrOrder
     * @param int $intCount
     * @param array $arrDetail
     * @return array
     */
    public function formatResult($arrOrder = array(), $intCount = 0, $arrDetail = array(),$skuInfos = array())
    {
        unset($arrOrder['id']);
        unset($arrOrder['version']);
        foreach ($arrDetail as &$value){
            $value['sku_net_text'] = $skuInfos[$value['sku_id']]['sku_net'].Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$skuInfos[$value['sku_id']]['sku_net_unit']];;
            $value['upc_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$value['upc_unit']];
            $value['sku_effect_type_text'] = Nscm_Define_Sku::SKU_EFFECT_TYPE_TEXT[$skuInfos[$value['sku_id']]['sku_effect_type']];
            $value['is_defective_text'] = Nscm_Define_Stock::QUALITY_TEXT_MAP[$value['is_defective']];
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['production_time']));
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['expiration_time']));
            }
        }
        $arrRet = $arrOrder;
        $arrRet['total'] = $intCount;
        $arrRet['shift_order_detail'] = array();
        $arrRet['shift_order_detail'] = $arrDetail;

        return $arrRet;
    }
}

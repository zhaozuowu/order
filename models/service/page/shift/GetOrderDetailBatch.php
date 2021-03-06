<?php
/**
 * Class Service_Page_Shift_GetOrderDetailBatch
 */

class Service_Page_Shift_GetOrderDetailBatch
{

    /**
     * @var Service_Data_ShiftOrder
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
        // 去掉前缀
        if(!empty($arrInput['shift_order_ids'])) {
            foreach ($arrInput['shift_order_ids'] as $intKey => $strShiftOrderId) {
                $arrInput['shift_order_ids'][$intKey] = intval(Order_Util::trimShiftOrderIdPrefix($strShiftOrderId));
            }
        }
        $arrOrders = $this->objShiftOrder->getByOrderIds($arrInput['shift_order_ids']);
        if(empty($arrOrders)) {
            return [];
        }

        $intOrderCount = count($arrOrders);
        $arrOrderDetail = $this->objShiftOrderDetail->getBatch($arrInput);
        $skuIds = array_column($arrOrderDetail,'sku_id');
        $skuInfos = $this->objSku->getSkuInfos($skuIds);

        return $this->formatResult($arrOrders, $intOrderCount, $arrOrderDetail,$skuInfos);
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
        $orderList = array();
        foreach ($arrOrder as $key => $order){
            unset($order['id']);
            unset($order['version']);
            unset($order['is_detete']);
            $orderId = $order['shift_order_id'];
            $order['shift_order_id'] = Nscm_Define_OrderPrefix::SHO . intval($order['shift_order_id']);
            $orderList[$orderId] = $order;
        }
        foreach ($arrDetail as &$value){
            unset($value['id']);
            unset($value['version']);
            unset($value['is_delete']);
            $value['sku_net_text'] = $skuInfos[$value['sku_id']]['sku_net'].Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$skuInfos[$value['sku_id']]['sku_net_unit']];;
            $value['upc_unit_text'] = Nscm_Define_Sku::UPC_UNIT_MAP[$value['upc_unit']];
            $value['sku_effect_type_text'] = Nscm_Define_Sku::SKU_EFFECT_TYPE_TEXT[$skuInfos[$value['sku_id']]['sku_effect_type']];
            $value['is_defective_text'] = Nscm_Define_Stock::QUALITY_TEXT_MAP[$value['is_defective']];
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['production_time']));
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $skuInfos[$value['sku_id']]['sku_effect_type']) {
                $value['production_or_expiration_time'] = strtotime(date('Y-m-d',$value['expiration_time']));
            }
            if(isset($orderList[$value['shift_order_id']])){
                $orderList[$value['shift_order_id']]['shift_order_detail'][] = $value;
            }
        }
        $arrRet['total'] = $intCount;
        $arrRet['shift_order_list'] = array_values($orderList);
        return $arrRet;
    }
}

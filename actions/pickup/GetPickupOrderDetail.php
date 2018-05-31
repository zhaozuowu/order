<?php
/**
 * @desc 获取拣货单详情
 * @date 2018/5/10
 * @author huabang.xue@ele.me
 */

class Action_GetPickupOrderDetail extends Order_Base_Action
{
    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->arrInputParams = [
            'pickup_order_id' => 'int|required',
        ];
        $this->objPage = new Service_Page_Pickup_GetPickupOrderDetail();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrRet = [];
        if (empty($data)) {
            return $arrRet;
        }

        $arrSkus = [];
        if (!empty($data['pickup_skus'])) {
            foreach ($data['pickup_skus'] as $arrSku) {
                $arrSkus[] = [
                    'upc_id' => $arrSku['upc_id'],
                    'sku_id' => $arrSku['sku_id'],
                    'sku_name' => $arrSku['sku_name'],
                    'sku_net' => $arrSku['sku_net'],
                    'sku_net_unit' => $arrSku['sku_net_unit'],
                    'sku_net_unit_text' => Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$arrSku['sku_net_unit']],
                    'upc_unit' => $arrSku['upc_unit'],
                    'upc_unit_text' => Nscm_Define_Sku::UPC_UNIT_MAP[$arrSku['upc_unit']],
                    'upc_unit_num' => $arrSku['upc_unit_num'],
                    'upc_unit_num_text' => '1*' . $arrSku['upc_unit_num'],
                    'order_amount' => $arrSku['order_amount'],
                    'distribute_amount' => $arrSku['distribute_amount'],
                    'pickup_amount' => $arrSku['pickup_amount'],
                    'recommend_pickup_extra_info' => $this->formatRecommendPickupExtraInfo($arrSku['pickup_extra_info'],$data['pickup_sku_effect_type_list'],$arrSku['sku_id']),
                    'pickup_extra_info' => $this->formatRealityPickupExtraInfo($arrSku['pickup_extra_info']),
                ];
            }
        }

        $arrRet['pickup_order_id'] = $data['pickup_order_id'];
        $arrRet['remark'] = $data['remark'];
        $arrRet['pickup_order_type'] = $data['pickup_order_type'];
        $arrRet['pickup_order_type_text'] = Order_Define_PickupOrder::PICKUP_ORDER_TYPE_MAP[$data['pickup_order_type']];
        $arrRet['pickup_order_status'] = $data['pickup_order_status'];
        $arrRet['pickup_order_status_text'] = Order_Define_PickupOrder::PICKUP_ORDER_STATUS_MAP[$data['pickup_order_status']];
        $arrRet['pickup_order_is_print'] = $data['pickup_order_is_print'];
        $arrRet['pickup_order_is_print_text'] = Order_Define_PickupOrder::PICKUP_ORDER_PRINT_MAP[$data['pickup_order_is_print']];
        $arrRet['stockout_order_amount'] = $data['stockout_order_amount'];
        $arrRet['sku_kind_amount'] = $data['sku_kind_amount'];
        $arrRet['sku_pickup_amount'] = $data['sku_pickup_amount'];
        $arrRet['sku_distribute_amount'] = $data['sku_distribute_amount'];
        $arrRet['creator'] = $data['creator'];
        $arrRet['create_time'] = $data['create_time'];
        $arrRet['update_operator'] = $data['update_operator'];
        $arrRet['update_time'] = $data['update_time'];
        $arrRet['warehouse_name'] = $data['warehouse_name'];
        $arrRet['pickup_skus'] = $arrSkus;

        return $arrRet;
    }

    private function formatRecommendPickupExtraInfo($pickupExtraInfo,$arrSkusInfo,$intSkuId)
    {
        $intSkuEffectType = isset($arrSkusInfo[$intSkuId]) ? $arrSkusInfo[$intSkuId]:0;
        $pickupExtraInfo =  json_decode($pickupExtraInfo, true);
        $list =  empty($pickupExtraInfo['create_info']) ? []:$pickupExtraInfo['create_info'];
        foreach ($list as $key=>$item) {
            $list[$key]['expire_time'] = 0;
            $list[$key]['time'] = 0;
            if (Nscm_Define_Sku::SKU_EFFECT_FROM == $intSkuEffectType) {

                $list[$key]['expire_time'] = $item['production_time'];
                $list[$key]['time'] = strtotime(date('Y-m-d',
                    $item['production_time']));
            } else if (Nscm_Define_Sku::SKU_EFFECT_TO == $intSkuEffectType) {
                $list[$key]['expire_time'] = $item['expiration_time'];
                $list[$key]['time'] = strtotime(date('Y-m-d',
                    $item['expiration_time']));
            }
        }
        return $list;
    }
    private function formatRealityPickupExtraInfo($pickupExtraInfo)
    {
        $pickupExtraInfo =  json_decode($pickupExtraInfo, true);
        return empty($pickupExtraInfo['finish_info']) ? []:$pickupExtraInfo['finish_info'];
    }
}
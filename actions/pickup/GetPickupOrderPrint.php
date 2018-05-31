<?php
/**
 * @name GetPickupOrderPrint.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/12 16:23
 */

class Action_GetPickupOrderPrint extends Order_Base_Action
{
    /**
     * params
     * @var array
     */
    protected $arrInputParams = [
        'pickup_order_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * constructor
     * @return mixed
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderPrint();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
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
                    'upc_unit_text' => Nscm_Define_Sku::UPC_UNIT_MAP[$arrSku['upc_unit_text']],
                    'upc_unit_num' => $arrSku['upc_unit_num'],
                    'order_amount' => $arrSku['order_amount'],
                    'distribute_amount' => $arrSku['distribute_amount'],
                    'pickup_amount' => $arrSku['pickup_amount'],
                    'pickup_extra_info' => json_decode($arrSku['pickup_extra_info'], true),
                ];
            }
        }

        $arrRet['pickup_order_id'] = $data['pickup_order_id'];
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
        $arrRet['pickup_skus'] = $arrSkus;

        return $arrRet;
    }
}
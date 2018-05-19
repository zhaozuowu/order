<?php
/**
 * @name GetPlaceOrderDetail.php
 * @desc GetPlaceOrderDetail.php
 * @author yu.jin03@ele.me
 */
class Action_GetPlaceOrderDetail extends Order_Base_Action
{
    /**
     * 参数数组
     * @var array
     */
    protected $arrInputParams = [
        'place_order_id' => 'int|required',
    ];

    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * init object
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_GetPlaceOrderDetail();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        if (empty($data)) {
            return [];
        }
        $data['is_defective_text'] = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_MAP[$data['is_defective']];
        $data['place_order_status_text'] = Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[$data['place_order_status']];
        $data['stockin_order_type_text'] = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_MAP[$data['stockin_order_type']];
        $data['create_time'] = date("Y-m-d H:i:s", $data['create_time']);
        foreach ((array)$data['skus'] as $intKey => $dataItem) {
            $data['skus'][$intKey]['upc_unit_text'] = Order_Define_Sku::UPC_UNIT_MAP[$dataItem['upc_unit']];
            $data['skus'][$intKey]['sku_effect_time'] = date("Y-m-d H:i:s", $dataItem['expire_date']);
            $data['skus'][$intKey]['upc_unit_num_text'] = '1*' . $dataItem['upc_unit_num'];
            $dataItem['actual_info'] = empty($dataItem['actual_info']) ?
                                                    '' : json_decode($dataItem['actual_info'], true);
            foreach ((array)$dataItem['actual_info'] as $intActualKey => $actualInfoItem) {
                $data['skus'][$intKey]['place_infos'][$intActualKey]['location_id'] = $actualInfoItem['place_location_id'];
                $data['skus'][$intKey]['place_infos'][$intActualKey]['place_amount'] = intval($actualInfoItem['place_amount']);
            }
        }
        return $data;
    }
}
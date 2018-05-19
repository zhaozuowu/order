<?php
/**
 * @name GetPlaceOrderPrint.php
 * @desc GetPlaceOrderPrint.php
 * @author yu.jin03@ele.me
 */
class Action_GetPlaceOrderPrint extends Order_Base_Action
{
    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * @var array
     */
    protected $arrInputParams = [
        'place_order_ids' => 'str|required',
    ];

    /**
     *
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_GetPlaceOrderPrint();
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
        foreach ((array)$data as $intKey => $dataItem) {
            $data[$intKey]['stockin_order_type_text']
                = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_MAP[$dataItem['stockin_order_type']];
            $data[$intKey]['is_defective_text']
                = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_MAP[$dataItem['is_defective']];
            foreach ((array)$data[$intKey]['skus'] as $intSkuKey => $skuItem) {
                $intUpcUnit = $skuItem['upc_unit'];
                $intUpcUnitNum = $skuItem['upc_unit_num'];
                $data[$intKey]['skus'][$intSkuKey]['upc_unit_text'] = Order_Define_Sku::UPC_UNIT_MAP[$intUpcUnit];
                $data[$intKey]['skus'][$intSkuKey]['upc_unit_num_text'] = '1*' . $intUpcUnitNum;
            }
        }
        return $data;
    }
}
<?php
/**
 * @desc 获取上架单列表
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Action_GetPlaceOrderList extends Order_Base_Action
{
    protected $boolCheckAuth = false;
    protected $boolCheckLogin = false;
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
            'place_order_status' => 'int|optional',
            'place_order_id'     => 'str|len[16]|min[4]|optional',
            'source_order_id'   => 'str|len[16]|min[4]|optional',
            'vendor_id'          => 'int|min[0]|optional',
            'create_time_start'  => 'int|min[0]|optional',
            'create_time_end'    => 'int|min[0]|optional',
            'place_time_start'   => 'int|min[0]|optional',
            'place_time_end'     => 'int|min[0]|optional',
            'page_num'           => 'int|default[1]|min[1]|optional',
            'page_size'          => 'int|required|min[1]|max[200]',

        ];

        $this->objPage = new Service_Page_Place_GetPlaceOrderList();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($arrData)
    {
        $ret = [];
        if (empty($arrData)) {
            return $ret;
        }
        foreach ((array)$arrData['orders'] as $intKey => $arrItem) {
            $arrData['orders'][$intKey]['create_time']
                = date('Y-m-d H:i:s', $arrItem['create_time']);
            $arrData['orders'][$intKey]['place_order_status_text']
                = Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[$arrItem['place_order_status']];
            $arrData['orders'][$intKey]['stockin_order_type_text']
                = Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_MAP[$arrItem['stockin_order_type']];
            $arrData['orders'][$intKey]['is_defective_text']
                = Order_Define_PlaceOrder::PLACE_ORDER_QUALITY_MAP[$arrItem['is_defective']];
        }
        return $arrData;
        //$arrFormatRet

    }

}
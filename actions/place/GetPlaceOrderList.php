<?php
/**
 * @desc 获取上架单列表
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Action_GetPlaceOrderList extends Order_Base_Action
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
            'place_order_status' => 'str|default['.implode(',',Order_Define_PlaceOrder::ALL_STATUS).']|optional',
            'place_order_id' => 'str|len[16]|min[4]|optional',
            'stockin_order_id' => 'str|len[16]|min[4]|optional',
            'vendor_id' => 'int|min[0]',
            'create_time_start' => 'int|min[0]',
            'create_time_end' => 'int|min[0]',
            'place_time_start' => 'int|min[0]',
            'place_time_end' => 'int|min[0]',
            'page_num' => 'int|default[1]|min[1]|optional',
            'page_size' => 'int|required|min[1]|max[200]',

        ];

        $this->objPage = new Service_Page_Place_GetPlaceOrderStatistics();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {

        $ret = [];
        if (empty($data)) {
            return $ret;
        }
        return $data;
        //$arrFormatRet

    }

}
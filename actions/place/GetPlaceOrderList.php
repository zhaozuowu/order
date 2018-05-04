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
     * add validate
     * @throws Wm_Error
     */
    public function beforeMyExecute()
    {
        parent::beforeMyExecute();
        $objValidator = new Wm_Validator();
        if (isset($this->arrFilterResult['place_order_id'])) {
            $strPlaceOrderId = $this->arrFilterResult['place_order_id'];
            $strPlaceOrderId = preg_replace('/[^0-9]/', '', $strPlaceOrderId);
            $objValidator->addValidator($strPlaceOrderId, $this->arrFilterResult['place_order_id'], 'str|len[13]|min[4]|optional', 'place_order_id param invalid');
        }
        if (isset($this->arrFilterResult['stockin_order_id'])) {
            $strStockinOrderId = $this->arrFilterResult['stockin_order_id'];
            $strStockinOrderId = preg_replace('/[^0-9]/', '', $strStockinOrderId);
            $objValidator->addValidator($strStockinOrderId, $this->arrFilterResult['stockin_order_id'], 'str|len[13]|min[4]|optional', 'stockin_order_id param invalid');
        }
        $objValidator->validate();
    }

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->arrInputParams = [
            'place_order_status' => 'int|optional',
            'place_order_id'     => 'str|len[16]|min[4]|optional',
            'stockin_order_id'   => 'str|len[16]|min[4]|optional',
            'vendor_id'          => 'int|min[0]|optional',
            'create_time_start'  => 'int|min[0]|optional',
            'create_time_end'    => 'int|min[0]|optional',
            'place_time_start'   => 'int|min[0]|optional',
            'place_time_end'     => 'int|min[0]|optional',
            'page_num'           => 'int|default[1]|min[1]|optional',
            'page_size'          => 'int|required|min[1]|max[200]',

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
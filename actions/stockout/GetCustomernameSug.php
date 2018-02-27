<?php
/**
 * @name Action_GetCustomernameSug
 * @desc 查询客户名称sug
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetCustomernameSug extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'order_type' => 'int|required',
    ];

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

        $this->objPage = new Service_Page_Stockout_GetCustomernameSug();
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
        $arrFormatRet['customer_list'] = [];
        foreach ($data as $arrRetItem ) {
             $arrFormatRetItem = [];
             $arrFormatRetItem['customer_name'] = empty($arrRetItem['customer_name']) ? '' :$arrRetItem['customer_name'];
             $arrFormatRetItem['customer_id'] = empty($arrRetItem['customer_id']) ? 0: intval($arrRetItem['customer_id']);
            $arrFormatRet['customer_list'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;


    }

}
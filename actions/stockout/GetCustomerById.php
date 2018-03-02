<?php
/**
 * @name Action_GetCustomerById
 * @desc 查询客户信息
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetCustomerById extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'customer_id' => 'int|required',
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

        $this->objPage = new Service_Page_Stockout_GetCustomerById();
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
        $arrFormatRet = [
            'customer_id' => empty($data['customer_id']) ? 0 : $data['customer_id'],
            'customer_name' => empty($data['customer_name']) ? '' : $data['customer_name'],
            'customer_contactor' => empty($data['customer_contactor']) ? '' : $data['customer_contactor'],
            'customer_contact' => empty($data['customer_contact']) ? '' : $data['customer_contact'],
            'customer_address' => empty($data['customer_address']) ? '' : $data['customer_address'],

        ];
        return $arrFormatRet;


    }

}
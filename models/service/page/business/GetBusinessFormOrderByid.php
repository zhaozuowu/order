<?php

/**
 * @name Service_Page_Business_GetBusinessFormOrderByid
 * @desc 查询业态订单明细
 * @author zhaozuowu@iwaimai.baidu.com
 */
class Service_Page_Business_GetBusinessFormOrderByid
{
    /**
     * @var Service_Data_BusinessFormOrder
     */
    protected $objData;

    /**
     * init
     */
    public function __construct()
    {
        $this->objData = new Service_Data_BusinessFormOrder();
    }


    /**
     * execute
     * @param $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $strId = isset($arrInput['business_form_order_id']) ? intval($arrInput['business_form_order_id']) : 0;
        return $this->objData->getBusinessFormOrderByid($strId);
    }
}
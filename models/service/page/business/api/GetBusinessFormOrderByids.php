<?php
/**
 * @name Service_Page_Business_Api_GetBusinessFormOrderByids
 * @desc 查询业态订单明细
 * @author huabang.xue@ele.me
 */

class Service_Page_Business_Api_GetBusinessFormOrderByids
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
        $arrIds = explode(',', $arrInput['business_form_order_ids']);
        return $this->objData->getBusinessFormOrderByIds($arrIds);
    }
}
<?php
/*
 * @file: CreateBusinessFormOrder.php
 * @Author: jinyu02 
 * @Date: 2017-12-26 15:36:39 
 * @Last Modified by:   jinyu02 
 * @Last Modified time: 2017-12-26 15:36:39 
 */
class Service_Page_Business_CreateBusinessFormOrder {
    
    /**
     * @var Service_Data_BusinessFormOrder
     */
    private $objDsBusinessFormOrder;
    
    /**
     * init
     */
    public function __construct() {
        $this->objDsBusinessFormOrder = new Service_Data_BusinessFormOrder();
    }
    
    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput) {
        return $this->objDsBusinessFormOrder->createBusinessFormOrder($arrInput);
    }
}
<?php
/**
 * @name Controller_StockoutService
 * @desc 创建出库单
 * @author  jinyu02@iwaimai.baidu.com
 */
class Controller_BusinessService {

    /**
     * @var Service_Page_Business_CreateBusinessFormOrder 
     */
    protected $objCreateBusinessFormOrder;

    /**
     * init
     */
    public function __construct() {
        $this->objCreateBusinessFormOrder = new Service_Page_Business_CreateBusinessFormOrder();
    }

    public function createBusinessFormOrder($request) {
        return $this->objCreateBusinessFormOrder->execute($request);
    } 
}

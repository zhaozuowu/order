<?php
/**
 * @name Service_Page_Frozen_CreateOrderBySystem
 * @desc 自动新建冻结单
 * @author ziliang.zhang02@ele.me
 */

class Service_Page_Frozen_CreateOrderBySystem
{
    /**
     * @var Service_Data_Frozen_StockFrozenOrder
     */
    protected $objStockFrozenOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockFrozenOrder = new Service_Data_Frozen_StockFrozenOrder();
    }

    /**
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute()
    {
        //系统自动创建冻结单
        $this->objStockFrozenOrder->createFrozenOrderBySystem();
    }

}

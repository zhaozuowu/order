<?php
/**
 * @name Service_Page_Frozen_CreateOrder
 * @desc 新建冻结单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Frozen_CreateOrder
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
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        // 生成一个冻结单号
        $arrInput['stock_frozen_order_id'] = Order_Util_Util::generateStockFrozenOrderId();
        Bd_Log::trace('generate stock frozen order id: ' . $arrInput['stock_frozen_order_id']);

        // 创建冻结单
        $arrOutput = $this->objStockFrozenOrder->createFrozenOrder($arrInput);
        return $arrOutput;
    }
}

<?php
/**
 * @name Service_Page_Adjust_CreateOrder
 * @desc 新建调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_CreateOrder
{
    /**
     * @var Service_Data_StockAdjustOrder
     */
    protected $objStockAdjustOrder;

    /**
     * @var Service_Data_StockAdjustOrderDetail
     */
    protected $objStockAdjustOrderDetail;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockAdjustOrder = new Service_Data_StockAdjustOrder();
        $this->objStock = new Service_Data_Stock();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        // 生成一个调整单号
        $arrInput['stock_adjust_order_id'] = Order_Util_Util::generateStockAdjustOrderId();

        // 创建调整单
        $arrOutput = $this->objStockAdjustOrder->createAdjustOrder($arrInput);
        return $arrOutput;
    }
}

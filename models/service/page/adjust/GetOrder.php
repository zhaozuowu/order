<?php
/**
 * @name Service_Page_Adjust_GetOrder
 * @desc 获取采购单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_GetOrder
{
    /**
     * adjust order data service
     * @var Service_Data_StockAdjustOrder
     */
    protected $objStockAdjustOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockAdjustOrder = new Service_Data_StockAdjustOrder();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        // 去掉SAO前缀
        if(!empty($arrInput['stock_adjust_order_id'])) {
            $arrInput['stock_adjust_order_id'] =
                intval(Order_Util::trimStockAdjustOrderIdPrefix(stock_adjust_order_id));
        }

        $intCount = $this->objStockAdjustOrder->getCount($arrInput);
        $arrOutput = $this->objStockAdjustOrder->get($arrInput);

        return array('total' => $intCount, 'stock_adjust_order_list' => $arrOutput);
    }
}

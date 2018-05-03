<?php
/**
 * @name Service_Page_Frozen_GetOrderById
 * @desc 根据ID获取冻结单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Frozen_GetOrderById
{
    /**
     * frozen order data service
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
        // 去掉前缀
        if(!empty($arrInput['stock_frozen_order_id'])) {
            $arrInput['stock_frozen_order_id'] =
                intval(Order_Util::trimStockFrozenOrderIdPrefix($arrInput['stock_frozen_order_id']));
        }

        $arrOutput = $this->objStockFrozenOrder->getOrderList($arrInput);

        if(!empty($arrOutput))
            return $arrOutput[0];

        return [];
    }
}

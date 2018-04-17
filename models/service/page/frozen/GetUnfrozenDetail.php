<?php
/**
 * @name Service_Page_Frozen_GetUnfrozenDetail
 * @desc 获取解冻明细
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Frozen_GetUnfrozenDetail
{
    /**
     * frozen order data service
     * @var Service_Data_Frozen_StockUnfrozenOrderDetail
     */
    protected $objStockUnfrozen;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockUnfrozen = new Service_Data_Frozen_StockUnfrozenOrderDetail();
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

        $intCount = $this->objStockUnfrozen->getOrderListCountGroupBySku($arrInput);
        $arrOutput = $this->objStockUnfrozen->getOrderListGroupBySku($arrInput);

        return array('total' => $intCount, 'list' => $arrOutput);
    }
}

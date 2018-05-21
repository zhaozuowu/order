<?php
/**
 * @name Service_Page_Frozen_GetOrder
 * @desc 获取冻结单列表
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Frozen_GetFrozenSku
{
    /**
     * frozen order data service
     * @var Service_Data_Frozen_StockFrozenOrderDetail
     */
    protected $objStockFrozenOrderDetail;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockFrozenOrderDetail = new Service_Data_Frozen_StockFrozenOrderDetail();
    }

    /**
     * execute
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        // 去掉前缀
        if(!empty($arrInput['stock_frozen_order_id'])) {
            $arrInput['stock_frozen_order_id'] =
                intval(Order_Util::trimStockFrozenOrderIdPrefix($arrInput['stock_frozen_order_id']));
        }

        $intCount = $this->objStockFrozenOrderDetail->getOrderListCountGroupBySku($arrInput);
        $arrOutput = $this->objStockFrozenOrderDetail->getOrderListGroupBySku($arrInput);

        return array('total' => $intCount, 'list' => $arrOutput);
    }
}

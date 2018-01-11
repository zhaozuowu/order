<?php
/**
 * @name Service_Page_Adjust_GetOrderDetail
 * @desc 查询采购单sku
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_GetOrderDetail
{
    /**
     * stock adjust order data service
     * @var Service_Data_StockAdjustOrder
     */
    protected $objStockAdjustOrder;


    /**
     * stock adjust order detail data service
     * @var Service_Data_StockAdjustOrderDetail
     */
    protected $objStockAdjustOrderDetail;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockAdjustOrder = new Service_Data_StockAdjustOrder();
        $this->objStockAdjustOrderDetail = new Service_Data_StockAdjustOrderDetail();
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
                intval(Order_Util::trimStockAdjustOrderIdPrefix($arrInput['stock_adjust_order_id']));
        }

        $arrOrder = $this->objStockAdjustOrder->getByOrderId($arrInput['stock_adjust_order_id']);
        if( (false === $arrOrder) || empty($arrOrder) ) {
            return [];
        }

        $intOrderDetailCount = $this->objStockAdjustOrderDetail->getCount($arrInput);
        $arrOrderDetail = $this->objStockAdjustOrderDetail->get($arrInput);

        return $this->formatResult($arrOrder, $intOrderDetailCount, $arrOrderDetail);
    }

    /**
     * 格式化输出返回结果
     * @param array $arrOrder
     * @param int $intCount
     * @param array $arrDetail
     * @return array
     */
    public function formatResult($arrOrder = array(), $intCount = 0, $arrDetail = array())
    {
        $arrRet = $arrOrder;
        $arrRet['stock_adjust_order_detail'] = array();
        $arrRet['stock_adjust_order_detail']['total'] = $intCount;
        $arrRet['stock_adjust_order_detail']['detail'] = $arrDetail;

        return $arrRet;
    }
}

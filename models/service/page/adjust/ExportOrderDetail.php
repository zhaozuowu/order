<?php
/**
 * @name Service_Page_Adjust_ExportOrderDetail
 * @desc 导出库存调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_ExportOrderDetail
{
    /**
     * adjust order data service
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
                intval(Order_Util::trimStockAdjustOrderIdPrefix(stock_adjust_order_id));
        }

        $intOrderDetailCount = $this->objStockAdjustOrderDetail->getCount($arrInput);
        $arrOrderDetail = $this->objStockAdjustOrderDetail->get($arrInput);

        if(empty($arrOrderDetail)) {
            return $this->formatResult();
        }

        $skuIds = array_unique(array_column($arrOrderDetail, 'sku_id'));
        $warehouseIds = array_unique(array_column($arrOrderDetail, 'warehouse_id'));
        $orderIds = array_unique(array_column($arrOrderDetail, 'stock_adjust_order_id'));


        $arrWarehouseList = $this->getWarehouseInfos($warehouseIds);

        $arrSkuList = $this->getSkuInfos($skuIds);

        $orderList = $this->getOrderInfos($orderIds);

        return $this->formatResult($arrSkuList, $arrWarehouseList, $intOrderDetailCount, $arrOrderDetail, $orderList);
    }

    /**
     * 格式化输出返回结果
     * @param array $arrSkuList
     * @param array $arrWarehouse
     * @param int $intCount
     * @param array $arrDetail
     * @param array $orderList
     * @return array
     */
    public function formatResult($arrSkuList = array(), $arrWarehouse = array(),
                                 $intCount = 0, $arrDetail = array(), $orderList = array())
    {
        $arrRet = [];
        if(empty($arrDetail)) {
            return $arrRet;
        }

        $arrRet['total'] = $intCount;
        $arrRet['list'] = [];

        foreach ($arrDetail as $detail) {
            $intSkuId = $detail['sku_id'];
            $intWarehouseId = $detail['warehouse_id'];
            $intOrderId = $detail['stock_adjust_order_id'];

            if(!empty($arrSkuList[$intSkuId])) {
                $arrSkuInfo = $arrSkuList[$intSkuId];
                $detail['sku_category_1_name']  = $arrSkuInfo['sku_category_1_name'];
                $detail['sku_category_2_name']  = $arrSkuInfo['sku_category_2_name'];
                $detail['sku_category_3_name']  = $arrSkuInfo['sku_category_3_name'];
                $detail['sku_from_country']     = $arrSkuInfo['sku_from_country'];
            }
            if(!empty($arrWarehouse[$intWarehouseId])) {
                $arrWarehouseInfo = $arrWarehouse[$intWarehouseId];
                $detail['warehouse_name'] = $arrWarehouseInfo['warehouse_name'];
                $detail['city_name'] = $arrWarehouseInfo['city_name'];
                $detail['city_id'] = $arrWarehouseInfo['city_id'];
            }
            if(!empty($orderList[$intOrderId])) {
                $arrOrder = $orderList[$intOrderId];
                $detail['creator_name'] = $arrOrder['creator_name'];
            }

            $arrRet['list'][] = $detail;
        }

        return $arrRet;
    }

    /**
     * 获取sku详情
     * @param array $arrSkuIds sku id 数组
     * @return array
     */
    protected function getSkuInfos($arrSkuIds) {
        if(empty($arrSkuIds)) {
            return [];
        }
        $arrSkuIds = array_unique($arrSkuIds);

        $daoRalSku = new Dao_Ral_Sku();
        $arrSkuInfos = $daoRalSku->getSkuInfos($arrSkuIds);
        return $arrSkuInfos;
    }

    /**
     * 获取仓库信息
     * @param $warehouseIds
     * @return mixed
     */
    protected function getWarehouseInfos($warehouseIds)
    {
        if(empty($warehouseIds)) {
            return [];
        }

        $warehouseIds = array_unique($warehouseIds);

        $ralWarehouse = new Dao_Ral_Order_Warehouse();
        $arrWarehouseInfos = $ralWarehouse->getWareHouseList($warehouseIds);
        return $arrWarehouseInfos;
    }

    protected function getOrderInfos($orderList)
    {
        $cond = [
            'stock_adjust_order_ids' => $orderList,
        ];
        $arrOrder = $this->objStockAdjustOrder->get($cond);
        return $this->getMap($arrOrder, 'stock_adjust_order_id');
    }

    /**
     * 将 $arr 转换成以 $arr[$key] 为键的map
     * @param $arr
     * @param $key
     * @return array
     */
    protected function getMap($arr, $key)
    {
        $arrRet = [];
        if(empty($arr)) {
            return $arrRet;
        }

        foreach ($arr as $value) {
            if(isset($value[$key])) {
                $arrRet[$value[$key]] = $value;
            }
            else {
                return [];
            }
        }

        return $arrRet;
    }
}
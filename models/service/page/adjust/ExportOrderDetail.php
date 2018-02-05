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
                intval(Order_Util::trimStockAdjustOrderIdPrefix($arrInput['stock_adjust_order_id']));
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

                if(!empty($arrSkuInfo['sku_category_text'])) {
                    list($detail['sku_category_1_name'],
                        $detail['sku_category_2_name'],
                        $detail['sku_category_3_name']) = explode(',', $arrSkuInfo['sku_category_text']);
                }

                if(!empty($arrSkuInfo['sku_from_country'])) {
                    $detail['sku_from_country_str'] = Order_Define_Sku::SKU_FROM_COUNTRY_MAP[$arrSkuInfo['sku_from_country']];
                }

                if(!empty($arrSkuInfo['sku_net_unit'])) {
                    $detail['sku_net_unit_str'] = Nscm_Define_Sku::SKU_NET_UNIT_TEXT[$arrSkuInfo['sku_net_unit']];
                }

                if(!empty($arrSkuInfo['min_upc'])) {
                    $detail['upc_id'] = $arrSkuInfo['min_upc']['upc_id'];
                    $detail['upc_unit_str'] = Order_Define_Sku::UPC_UNIT_MAP[$arrSkuInfo['min_upc']['upc_unit']];
                }
            }
            if(!empty($arrWarehouse[$intWarehouseId])) {
                $arrWarehouseInfo = $arrWarehouse[$intWarehouseId];
                $detail['warehouse_name'] = $arrWarehouseInfo['warehouse_name'];
                $detail['city_name'] = $arrWarehouseInfo['city']['name'];
                $detail['city_id'] = $arrWarehouseInfo['city']['id'];
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
        if(empty($arrSkuInfos)) {
            Bd_Log::warning('get sku info failed. call ral failed', Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED, $arrSkuIds);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED);
        }
        return $arrSkuInfos;
    }

    /**
     * 获取仓库信息
     * @param $warehouseIds
     * @return mixed
     */
    protected function getWarehouseInfos($warehouseIds)
    {
        Bd_Log::debug(__METHOD__ . ' param ' . print_r($warehouseIds, true));
        if(empty($warehouseIds)) {
            return [];
        }

        $warehouseIds = array_unique($warehouseIds);

        $ralWarehouse = new Dao_Ral_Order_Warehouse();
        $arrWarehouseInfos = $ralWarehouse->getWareHouseList($warehouseIds);

        Bd_Log::trace(__METHOD__ . ' ret ' . json_encode($arrWarehouseInfos));

        if(empty($arrWarehouseInfos) || empty($arrWarehouseInfos['query_result'])) {
            Bd_Log::warning('get warehouse info failed. call ral failed' . json_encode($warehouseIds));
            return [];
        }

        return $this->getMap($arrWarehouseInfos['query_result'], 'warehouse_id');
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
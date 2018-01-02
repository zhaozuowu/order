<?php

/*
 * @file: Business.php
 * @Author: jinyu02 
 * @Date: 2017-12-26 15:36:39 
 * @Last Modified by: jinyu02
 * @Last Modified time: 2017-12-27 17:08:46
 */

class Service_Data_BusinessFormOrder
{

    /**
     * 创建业态订单
     * @param array $arrInput
     * @return bool
     */
    public function createBusinessFormOrder($arrInput)
    {
        $this->checkCreateParams($arrInput);
        $boolCreateFlag = Model_Orm_BusinessFormOrder::getConnection()->transaction(function() use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            $objBusinessFormOrder = new Model_Orm_BusinessFormOrder();
            $objBusinessFormOrder->create($arrCreateParams, false);
            $this->createBusinessFormOrderSku($arrInput['skus']);
        });
        if (!$boolCreateFlag) {
            Order_BusinessError::throwException(NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR);
        }
    }

    /**
     * 创建业态订单商品关联
     * @param array $arrSkus
     * @return void
     */
    public function createBusinessFormOrderSku($arrSkus)
    {
        $arrBatchSkuCreateParams = $this->getBatchSkuCreateParams($arrSkus);
        if (empty($arrBatchSkuCreateParams)) {
            return [];
        }
        return Model_Orm_StockoutOrderSku::batchInsert($arrBatchSkuCreateParams, false);
    }

    /**
     * 校验创建参数
     * @param array $arrInput
     * @return void
     */
    public function checkCreateParams($arrInput) {
        if ($arrInput['business_form_order_type']
            && !isset(Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrInput['business_form_order_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR);
        }
        if ($arrInput['order_supply_type']
            && !isset(Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrInput['order_supply_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR);
        }
    }

    /**
     * 获取业态订单创建参数
     * @param array $arrInput
     * @return array
     */
    public function getCreateParams($arrInput)
    {
        $arrCreateParams = [];
        if (empty($arrInput)) {
            return $arrCreateParams;
        }
        $arrCreateParams['business_form_order_id'] = Order_Util_Util::generateBusinessFormOrderId();
        if (!empty($arrInput['business_form_order_type'])) {
            $arrCreateParams['business_form_order_type'] = intval($arrInput['business_form_order_type']);
        }
        if (!empty($arrInput['business_form_order_price'])) {
            $arrCreateParams['business_form_order_price'] = intval($arrInput['business_form_order_price']);
        }
        if (!empty($arrInput['business_form_order_remark'])) {
            $arrCreateParams['business_form_order_remark'] = strval($arrInput['business_form_order_remark']);
        }
        if (!empty($arrInput['customer_id'])) {
            $arrCreateParams['customer_id'] = intval($arrInput['customer_id']);
        }
        if (!empty($arrInput['customer_name'])) {
            $arrCreateParams['customer_name'] = strval($arrInput['customer_name']);
        }
        if (!empty($arrInput['customer_contactor'])) {
            $arrCreateParams['customer_contact'] = strval($arrInput['customer_contactor']);
        }
        if (!empty($arrInput['customer_contact'])) {
            $arrCreateParams['customer_contact'] = strval($arrInput['customer_contact']);
        }
        if (!empty($arrInput['customer_address'])) {
            $arrCreateParams['customer_address'] = strval($arrInput['customer_address']);
        }
        if (!empty($arrInput['customer_location'])) {
            $arrCreateParams['customer_location'] = strval($arrInput['customer_location']);
        }
        if (!empty($arrInput['location_source'])) {
            $arrCreateParams['location_source'] = intval($arrInput['location_source']);
        }
        if (!empty($arrInput['customer_city_id'])) {
            $arrCreateParams['customer_city_id'] = intval($arrInput['customer_city_id']);
        }
        if (!empty($arrInput['customer_city_name'])) {
            $arrCreateParams['customer_city_name'] = strval($arrInput['customer_city_name']);
        }
        return $arrCreateParams;
    }

    /**
     * 创建业态订单商品参数
     * @param array $arrInput
     * @return array
     */
    public function getBatchSkuCreateParams($arrSkus)
    {
        $arrBatchSkuCreateParams = [];
        if (empty($arrSkus)) {
            return $arrBatchSkuCreateParams;
        }
        foreach ($arrSkus as $arrItem) {
            $arrSkuCreateParams = [];
            if (!empty($arrItem['sku_id'])) {
                $arrSkuCreateParams['sku_id'] = intval($arrItem['sku_id']);
            }
            if (!empty($arrItem['upc_id'])) {
                $arrSkuCreateParams['upc_id'] = strval($arrItem['upc_id']);
            }
            if (!empty($arrItem['order_amount'])) {
                $arrSkuCreateParams['order_amount'] = intval($arrItem['order_amount']);
            }
            if (!empty($arrItem['display_type'])) {
                $arrSkuCreateParams['display_type'] = intval($arrItem['display_type']);
            }
            if (!empty($arrItem['display_floor'])) {
                $arrSkuCreateParams['display_floor'] = intval($arrItem['display_floor']);
            }
            $arrBatchSkuCreateParams[] = $arrSkuCreateParams;
        }
        return $arrBatchSkuCreateParams;
    }

    /**
     * 获取出库单创建参数
     * @param array $arrInput
     * @return array
     */
    public function getStockoutCreateParams($arrInput) {
        $arrStockoutCreateParams = $this->getCreateParams($arrInput);
        $arrStockoutCreateParams['skus'] = $this->getBatchSkuCreateParams($arrInput['skus']);
        $arrStockoutCreateParams['stockout_order_id'] = Order_Util_Util::generateStockoutOrderId();
        return $arrStockoutCreateParams;
    }


    /**
     * 获取业态订单总数
     * @param array $arrInput
     * @return int
     */
    public function getBusinessFormOrderCount($arrInput)
    {
        $arrConditions = $this->getListConditions($arrInput);
        if (false === $arrConditions) {
            return 0;
        }
        return Model_Orm_BusinessFormOrder::count($arrConditions);
    }

    /**
     * 获取业态订单列表
     * @param $arrInput
     * @return array
     */
    public function getBusinessFormOrderList($arrInput)
    {
        $arrConditions = $this->getListConditions($arrInput);
        if (false === $arrConditions) {
            return [];
        }

        $arrInput['page_num'] = empty($arrInput['page_num']) ? 1 : intval($arrInput['page_num']);
        $intLimit = intval($arrInput['page_size']);
        $intOffset = (intval($arrInput['page_num']) - 1) * $intLimit;
        $arrBusinessFormOrderList = Model_Orm_BusinessFormOrder::getBusinessFormOrderListByConditions($arrConditions, [], $intOffset, $intLimit);
        if (empty($arrBusinessFormOrderList)) {
            return [];
        }
        $arrWarehouseIds = array_column($arrBusinessFormOrderList, 'warehouse_id');
        $arrOrderIds = array_column($arrBusinessFormOrderList, 'business_form_order_id');
        $objWarehouseRal = new Dao_Ral_Order_Warehouse();
        $arrWarehouseList = $objWarehouseRal->getWareHouseList($arrWarehouseIds);
        $arrWarehouseList = !empty($arrWarehouseList) ? array_column($arrWarehouseList, null, 'warehouse_id') : [];
        $colums = ['business_form_order_id', 'sum(order_amount) as  order_amount', 'sum(distribute_amount) as distribute_amount '];
        $arrSkuConditions['business_form_order_id'] = ['in', $arrOrderIds];
        $arrSkuList = Model_Orm_BusinessFormOrderSku::find($arrSkuConditions)->select($colums)->groupBy(['business_form_order_id'])->rows();
        $arrSkuList = array_column($arrSkuList, null, 'business_form_order_id');
        foreach ($arrBusinessFormOrderList as $key => $item) {
            $arrBusinessFormOrderList[$key]['warehouse_name'] = isset($arrWarehouseList[$item['warehouse_id']]) ? $arrWarehouseList[$item['warehouse_id']]['warehouse_name'] : '';
            $arrBusinessFormOrderList[$key]['order_amount'] = isset($arrSkuList[$item['business_form_order_id']]) ? $arrSkuList[$item['business_form_order_id']]['order_amount'] : 0;
            $arrBusinessFormOrderList[$key]['distribute_amount'] = isset($arrSkuList[$item['business_form_order_id']]) ? $arrSkuList[$item['business_form_order_id']]['distribute_amount'] : 0;

        }
        return $arrBusinessFormOrderList;

    }

    /**
     * 获取查询的条件
     *
     * @param array $arrInput
     * @return array
     */
    public function getListConditions($arrInput)
    {
        $arrConditions = [];
        $arrConditions['is_delete'] = Order_Define_Const::NOT_DELETE;
        if (!empty($arrInput['warehouse_id	'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_id']);
            $arrConditions['warehouse_id'] = ['in', $arrWareHouseIds];
        }
        if (!empty($arrInput['status'])) {
            $arrConditions['status'] = $arrInput['status'];
        }
        if (!empty($arrInput['business_form_order_id'])) {

            $arrConditions['business_form_order_id'] = $arrInput['business_form_order_id'];
        }
        if (!empty($arrInput['business_form_order_status'])) {
            $arrConditions['status'] = $arrInput['business_form_order_status'];
        }

        if (!empty($arrInput['business_form_order_type'])) {
            $arrConditions['business_form_order_type'] = $arrInput['business_form_order_type'];
        }
        if (!empty($arrInput['customer_name'])) {
            $arrConditions['customer_name'] = ['like', $arrInput['customer_name'] . '%'];
        }
        if (!empty($arrInput['customer_id'])) {
            $arrConditions['customer_id'] = $arrInput['customer_id'];
        }
        if (!empty($arrInput['start_time'])) {
            $arrConditions['quotation_end_time'] = ['>=', $arrInput['start_time']];
        }
        if (!empty($arrInput['end_time'])) {
            $arrConditions['quotation_start_time'] = ['<=', $arrInput['end_time']];
        }
        return $arrConditions;
    }

    /**
     * 根据业态订单id查询业态订单明细
     * @param $strOrderid
     * @return array|Model_Orm_BusinessFormOrder
     */
    public function getBusinessFormOrderByid($strOrderid)
    {
        $ret = [];
        if (empty($strOrderid)) {
            return $ret;
        }
        $arrBusFormOrderList = Model_Orm_BusinessFormOrder::getBusinessFormOrderByOrderId($strOrderid);
        if (empty($arrBusFormOrderList)) {
            return $ret;
        }
        $objWarehouseRal = new Dao_Ral_Order_Warehouse();
        $arrWarehouseList = $objWarehouseRal->getWareHouseList($arrBusFormOrderList['warehouse_id']);
        $arrWarehouseList =!empty($arrWarehouseList)? array_column($arrWarehouseList, null, 'warehouse_id'):[];
        $arrBusFormOrderList['warehouse_name'] = isset($arrWarehouseList[$arrBusFormOrderList['warehouse_id']]) ? $arrWarehouseList[$arrBusFormOrderList['warehouse_id']['warehouse_name']] : '';
        $arrColumns = [
            'sum(order_amount) as  order_amount',
            'sum(distribute_amount) as distribute_amount',
        ];
        $arrSkuConditions = [
            'business_form_order_id' => $arrBusFormOrderList['business_form_order_id']
        ];
        $arrSkuList = Model_Orm_BusinessFormOrderSku::find($arrSkuConditions)->select($arrColumns)->groupBy(['business_form_order_id'])->row();
        if (empty($arrSkuList)) {
            return $arrBusFormOrderList;
        }
        $arrBusFormOrderList = array_merge($arrBusFormOrderList, $arrSkuList);
        $skuInfo = Model_Orm_BusinessFormOrderSku::getBusSkuListByConditions($arrSkuConditions);
        $arrBusFormOrderList['skus'] = $skuInfo;
        return $arrBusFormOrderList;

    }


}
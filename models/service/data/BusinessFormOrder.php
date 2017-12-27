<?php

/*
 * @file: Business.php
 * @Author: jinyu02 
 * @Date: 2017-12-26 15:36:39 
 * @Last Modified by: jinyu02
 * @Last Modified time: 2017-12-26 15:45:02
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
        return Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($arrInput) {
            $arrCreateParams = $this->getCreateParams($arrInput);
            $objBusinessFormOrder = new Model_Orm_BusinessFormOrder();
            $objBusinessFormOrder->create($arrCreateParams, false);
            $this->createBusinessFormOrderSku($arrInput['skus']);
        });
    }

    /**
     * 创建业态订单商品关联
     * @param array $arrSkus
     * @return void
     */
    public function createBusinessFormOrderSku($arrSkus)
    {
        $arrBatchSkuCreateParams = $this->getSkuCreateParams($arrSkus);
        if (empty($arrBatchSkuCreateParams)) {
            return [];
        }
        return Model_Orm_StockoutOrderSku::batchInsert($arrBatchSkuCreateParams, false);
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
        $arrCreateParams['business_form_order_id'] = '11111111';
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
}
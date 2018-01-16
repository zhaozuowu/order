<?php
/**
 * @name Service_Data_StockoutDetail
 * @desc 销售出库明细表
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Service_Data_StockoutDetail
{

    /**
     * @var Dao_Ral_Stock
     */
    protected $objOrm;
    
    /**
     * init
     */
    public function __construct() {
        $this->objDaoStock = new Model_Orm_StockoutOrderDetail();
        $this->objDataStockout = new Service_Data_StockoutOrder();
    }



    /**
     * 销售出库明细总数
     * @param array $arrInput
     * @return int
     */
    public function getStockoutDetailCount($arrInput)
    {
        $arrConditions = $this->getListConditions($arrInput);
        if (false === $arrConditions) {
            return 0;
        }
        return Model_Orm_StockoutOrderDetail::count($arrConditions);
    }

    /**
     * 销售出库明细
     * @param $arrInput
     * @return array
     */
    public function getStockoutDetail($arrInput)
    {

        $arrConditions = $this->getListConditions($arrInput);
        $arrInput['page_num'] = empty($arrInput['page_num']) ? 1 : intval($arrInput['page_num']);
        $intLimit = intval($arrInput['page_size']);
        $intOffset = (intval($arrInput['page_num']) - 1) * $intLimit;
        $arrBusinessFormOrderList = Model_Orm_StockoutOrderDetail::getStockoutDetailByConditions($arrConditions, [], $intOffset, $intLimit);
        if (empty($arrBusinessFormOrderList)) {
            return [];
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
        if (!empty($arrInput['warehouse_id'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_id']);
            $arrConditions['warehouse_id'] = ['in', $arrWareHouseIds];
        }
        if (!empty($arrInput['warehouse_ids'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_ids']);
            $arrConditions['warehouse_id'] = ['in', $arrWareHouseIds];
        }
        if (!empty($arrInput['stockout_order_id'])) {
            $arrInput['stockout_order_id'] = ltrim($arrInput['stockout_order_id'], 'SSO');
            $arrConditions['stockout_order_id'] = $arrInput['stockout_order_id'];
        }
        if (!empty($arrInput['business_form_order_id'])) {
            $arrConditions['business_form_order_id'] = $arrInput['business_form_order_id'];
        }
        if (!empty($arrInput['sku_name'])) {
            $arrConditions['sku_name'] = ['like', $arrInput['sku_name'] . '%'];
        }
        if (!empty($arrInput['sku_id'])) {
            $arrConditions['sku_id'] = $arrInput['sku_id'];
        }
        if (!empty($arrInput['customer_id'])) {
            $arrConditions['customer_id'] = $arrInput['customer_id'];
        }
        if (!empty($arrInput['customer_name'])) {
            $arrConditions['customer_name'] = $arrInput['customer_name'];
        }
        if (!empty($arrInput['start_time'])) {
            $arrConditions['create_time'][] = ['>=', $arrInput['start_time']];
        }
        if (!empty($arrInput['end_time'])) {
            $arrConditions['create_time'][] = ['<=', $arrInput['end_time']];
        }

        if (!empty($arrInput['order_create_start_time'])) {
            $arrConditions['order_create_time'][] = ['>=', $arrInput['order_create_start_time']];
        }

        if (!empty($arrInput['order_create_end_time'])) {
            $arrConditions['order_create_time'][] = ['<=', $arrInput['order_create_end_time']];
        }
        if (!empty($arrInput['stockout_order_ids'])) {
            $arrInput['stockout_order_ids'] = is_array($arrInput['stockout_order_ids'])? $arrInput['stockout_order_ids']:explode(',', $arrInput['stockout_order_ids']);
            $arrInput['stockout_order_ids'] = $this->objDataStockout->batchTrimStockoutOrderIdPrefix($arrInput['stockout_order_ids']);
            $arrConditions['stockout_order_id'] = ['in', $arrInput['stockout_order_ids']];

        }
        return $arrConditions;
    }



}
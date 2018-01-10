<?php
/**
 * @name Service_Data_BusinessFormOrder
 * @desc business form order service data
 * @author jinyu02@iwaimai.baidu.com
 */

class Service_Data_BusinessFormOrder
{

    /**
     * @var Dao_Ral_Stock
     */
    protected $objDaoStock;
    
    /**
     * init
     */
    public function __construct() {
        $this->objDaoStock = new Dao_Ral_Stock();
    }
    
    /**
     * 创建业态订单
     * @param array $arrInput
     * @return integer
     */
    public function createBusinessFormOrder($arrInput)
    {
        //参数校验并拼接参数
        $this->checkCreateParams($arrInput);
        $arrCreateParams = $this->getCreateParams($arrInput);
        $arrBatchSkuCreateParams = $this->getBatchSkuCreateParams($arrCreateParams['business_form_order_id'], $arrInput['skus']);
        if (empty($arrCreateParams) || empty($arrBatchSkuCreateParams)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR);
        }
        //锁定库存
        list($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail) = $this->getFreezeStockParams($arrInput);
        //$this->objDaoStock->freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail);
        //创建业态订单和关联商品
        Model_Orm_BusinessFormOrder::getConnection()->transaction(function() use ($arrCreateParams, $arrBatchSkuCreateParams) {
            $objBusinessFormOrder = new Model_Orm_BusinessFormOrder();
            $objBusinessFormOrder->create($arrCreateParams, false);
            Model_Orm_BusinessFormOrderSku::batchInsert($arrBatchSkuCreateParams, false);
        });
        return $arrCreateParams['business_form_order_id'];
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
     * 拼接冻结库存参数
     * @param array $arrSkus
     * @return array
     */
    public function getFreezeStockParams($arrInput) {
        $intStockoutOrderId = 0;
        $intWarehouseId = 0;
        $arrFreezeStockDetail = [];
        if (empty($arrInput) || empty($arrInput['skus'])) {
            return [$intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail];
        }
        $intStockoutOrderId = $arrInput['stockout_order_id'];
        $intWarehouseId = $arrInput['warehouse_id'];
        foreach((array)$arrInput['skus'] as $arrSkuItem) {
            $arrFreezeStockItem = [];
            $arrFreezeStockItem['sku_id'] = $arrSkuItem['sku_id'];
            $arrFreezeStockItem['frozen_amount'] = $arrSkuItem['order_amount'];
            $arrFreezeStockDetail[] = $arrFreezeStockItem;
        }
        return [$intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail];
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
        return $arrCreateParams;
    }

    /**
     * 创建业态订单商品参数
     * @param array $arrInput
     * @return array
     */
    public function getBatchSkuCreateParams($intBusinessFormOrderId, $arrSkus)
    {
        $arrBatchSkuCreateParams = [];
        if (empty($arrSkus)) {
            return $arrBatchSkuCreateParams;
        }
        foreach ((array)$arrSkus as $arrItem) {
            $arrSkuCreateParams = [];
            if (!empty($arrItem['sku_id'])) {
                $arrSkuCreateParams['sku_id'] = intval($arrItem['sku_id']);
            }
            if (!empty($arrItem['order_amount'])) {
                $arrSkuCreateParams['order_amount'] = intval($arrItem['order_amount']);
            }
            $arrSkuCreateParams['business_form_order_id'] = $intBusinessFormOrderId;
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
            $arrConditions['create_time'][] = ['>=', $arrInput['start_time']];
        }
        if (!empty($arrInput['end_time'])) {
            $arrConditions['create_time'][] = ['<=', $arrInput['end_time']];
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
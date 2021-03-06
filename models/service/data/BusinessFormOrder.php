<?php
/**
 * @name Service_Data_BusinessFormOrder
 * @desc business form order service data
 * @author jinyu02@iwaimai.baidu.com
 */
class Service_Data_BusinessFormOrder
{
    /**
     * @var Dao_Huskar_Stock
     */
    protected $objHuskarStock;

    /**
     * @var Dao_Ral_Stock
     */
    protected $objDaoStock;

    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * @var Dao_Ral_Order_Warehouse
     */
    protected $objWarehouseRal;
    
    /**
     * init
     */
    public function __construct() {
        $this->objDaoStock = new Dao_Ral_Stock();
        $this->objHuskarStock = new Dao_Huskar_Stock();
        $this->objDaoSku = new Dao_Ral_Sku();
        $this->objWarehouseRal = new Dao_Ral_Order_Warehouse();
    }

    /**
     * 创建业态订单
     * @param array $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function createBusinessFormOrder($arrInput)
    {
        //参数校验并拼接参数
        $arrInput['business_form_order_status'] = Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_SUCCESS;
        $arrInput = $this->checkSkuBusinessForm($arrInput);
        $arrInput = $this->appendWarehouseInfoToOrder($arrInput);
        //锁定库存
        if (Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_SUCCESS
            == $arrInput['business_form_order_status']) {
            list($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail) = $this->getFreezeStockParams($arrInput);
            $arrStockRet = $this->objHuskarStock->freezeSkuStock($intStockoutOrderId, $intWarehouseId, $arrFreezeStockDetail);
            if (Order_Error_Code::STOCK_LOCK_CONFLICT == $arrStockRet['error_no']) {
                Order_Exception_Collector::clearAllException();
                Order_Exception_Collector::addException($intStockoutOrderId, 0, '',
                    Order_Exception_Const::CONCRETE_STOCK_LOCK_CONFLICT, 0);
                Order_BusinessError::throwException(Order_Error_Code::STOCK_FREEZE_ERROR);
            }
            $arrStockSkus = $arrStockRet['result'];
            $arrInput = $this->appendStockSkuInfoToOrder($arrInput, $arrStockSkus);
            $arrInput = $this->appendSkuTotalAmountToOrder($arrInput);
        }
        //构造订单和关联商品参数
        $arrCreateParams = $this->getCreateParams($arrInput);
        $arrBatchSkuCreateParams = $this->getBatchSkuCreateParams($arrCreateParams['business_form_order_id'], $arrInput['skus']);
        if (empty($arrCreateParams) || empty($arrBatchSkuCreateParams)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR);
        }
        //创建业态订单和关联商品
        Model_Orm_BusinessFormOrder::getConnection()->transaction(function() use ($arrCreateParams, $arrBatchSkuCreateParams) {
            $objBusinessFormOrder = new Model_Orm_BusinessFormOrder();
            $objBusinessFormOrder->create($arrCreateParams, false);
            Model_Orm_BusinessFormOrderSku::batchInsert($arrBatchSkuCreateParams, false);
        });
        $arrInput['business_form_order_id'] = $arrCreateParams['business_form_order_id'];
        $arrInput['orderTime'] = time(); //回传给tms的下单时间
        return $arrInput;
    }

    /**
     * 新增库存信息到商品
     * @param $arrInput
     * @return $arrInput
     */
    public function appendStockSkuInfoToOrder($arrInput, $arrStockSkus) {
        //如果库存为空标记为创建失败
        if (empty($arrInput['skus']) || empty($arrStockSkus)) {
            $arrInput['business_form_order_status'] =
                Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_FAILED;
        }
        $arrMapSkuIdToStockInfo = Order_Util_Util::arrayToKeyValue($arrStockSkus, 'sku_id');
        $arrOrderSkus = $arrInput['skus'];
        foreach ((array)$arrOrderSkus as $intKey => $arrSkuItem) {
            $intSkuId = $arrSkuItem['sku_id'];
            if (empty($intSkuId)) {
                continue;
            }
            if (empty($arrMapSkuIdToStockInfo[$intSkuId]['frozen_amount'])) {
                Order_Exception_Collector::addException($arrInput['stockout_order_id'], $intSkuId,
                    $arrSkuItem['sku_name'], Order_Exception_Const::CONCRETE_STOCK_NOT_ENOUGH);
            }
            $arrOrderSkus[$intKey]['distribute_amount'] = intval($arrMapSkuIdToStockInfo[$intSkuId]['frozen_amount']);
            $arrOrderSkus[$intKey]['cost_price'] = intval($arrMapSkuIdToStockInfo[$intSkuId]['cost_unit_price']);
            $arrOrderSkus[$intKey]['cost_total_price'] =
                $arrOrderSkus[$intKey]['cost_price']*$arrOrderSkus[$intKey]['distribute_amount'];
            $arrOrderSkus[$intKey]['cost_price_tax'] = intval($arrMapSkuIdToStockInfo[$intSkuId]['cost_unit_price_tax']);
            $arrOrderSkus[$intKey]['cost_total_price_tax'] =
                $arrOrderSkus[$intKey]['cost_price_tax']*$arrOrderSkus[$intKey]['distribute_amount'];
            //通过sku业态信息获取配货价格
            $arrSendPriceInfo = $arrOrderSkus[$intKey]['send_price_info'];
            $arrOrderSkus[$intKey]['send_price'] = 0;
            $arrOrderSkus[$intKey]['send_price_tax'] = 0;
            if (Order_Define_Sku::SKU_PRICE_TYPE_BENEFIT
                == $arrSendPriceInfo['sku_price_type']) {
                $arrOrderSkus[$intKey]['send_price'] = $arrOrderSkus[$intKey]['cost_price']
                                                        * (1 + $arrSendPriceInfo['sku_price_value']/100);
                $arrOrderSkus[$intKey]['send_price_tax'] = $arrOrderSkus[$intKey]['cost_price_tax']
                    * (1 + $arrSendPriceInfo['sku_price_value']/100.0);
            }
            if (Order_Define_Sku::SKU_PRICE_TYPE_COST
                == $arrSendPriceInfo['sku_price_type']) {
                $arrOrderSkus[$intKey]['send_price'] = $arrOrderSkus[$intKey]['cost_price'];
                $arrOrderSkus[$intKey]['send_price_tax'] = $arrOrderSkus[$intKey]['cost_price_tax'];
            }
            if (Order_Define_Sku::SKU_PRICE_TYPE_STABLE
                == $arrSendPriceInfo['sku_price_type']) {
                $arrOrderSkus[$intKey]['send_price_tax'] = intval($arrSendPriceInfo['sku_price_value']);
                $intTaxRate = $arrOrderSkus[$intKey]['sku_tax_rate'];
                $arrOrderSkus[$intKey]['send_price'] = $arrOrderSkus[$intKey]['send_price_tax']
                                    /(1 + Order_Define_Sku::SKU_TAX_NUM[$intTaxRate]/100.0);
            }
            $arrOrderSkus[$intKey]['send_total_price'] = $arrOrderSkus[$intKey]['send_price']
                                                            *$arrOrderSkus[$intKey]['distribute_amount'];
            $arrOrderSkus[$intKey]['send_total_price_tax'] = $arrOrderSkus[$intKey]['send_price_tax']
                                                            *$arrOrderSkus[$intKey]['distribute_amount'];
        }
        $arrInput['skus'] = $arrOrderSkus;
        return $arrInput;
    }

    /**
     * 添加总数到订单信息
     * @param $arrInput
     * @return mixed $arrInput
     * @throws Order_BusinessError
     */
    public function appendSkuTotalAmountToOrder($arrInput) {
        if (empty($arrInput['skus'])) {
            return $arrInput;
        }
        $arrSkus = $arrInput['skus'];
        $intTotalOrderAmount = 0;
        foreach ((array)$arrSkus as $arrSkuItem) {
            $intTotalOrderAmount += $arrSkuItem['order_amount'];
        }
        $arrInput['stockout_order_amount'] = $intTotalOrderAmount;
        $intTotalDistributeAmount = 0;
        $intTotalPrice = 0;
        foreach ((array)$arrSkus as $arrSkuItem) {
            $intTotalDistributeAmount += $arrSkuItem['distribute_amount'];
            $intTotalPrice += $arrSkuItem['send_total_price_tax'];
        }
        $arrInput['stockout_order_distribute_amount'] = $intTotalDistributeAmount;
        $arrInput['stockout_order_total_price'] = $intTotalPrice;
        if (0 == $arrInput['stockout_order_distribute_amount']) {
            $arrInput['business_form_order_status'] = Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_FAILED;
            Bd_Log::trace(sprintf("method[%s],create business form order failed because no available stock",
                            __METHOD__));
        }
        return $arrInput;
    }

    /**
     * 校验sku是否支持业态
     * @param $arrInput
     * @return mixed $arrInput
     * @throws Order_BusinessError
     */
    public function checkSkuBusinessForm($arrInput) {
        if (empty($arrInput['skus'])) {
            return $arrInput;
        }
        $arrSkus = $arrInput['skus'];
        $boolBusinessFormSupport = false;
        foreach ((array)$arrSkus as $arrSkuItem) {
            $arrSkuBusinessForm = explode(',', $arrSkuItem['sku_business_form']);
            if (in_array($arrInput['business_form_order_type'], $arrSkuBusinessForm)) {
                $boolBusinessFormSupport = true;
            }
        }
        if (!$boolBusinessFormSupport) {
            $arrInput['business_form_order_status'] = Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_FAILED;
            Bd_Log::trace(sprintf("method[%s] create business form order failed because no support sku",
                                    __METHOD__));
        }
        return $arrInput;
    }

    /**
     * 获取订单的操作信息
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function appendWarehouseInfoToOrder($arrInput) {
        $arrRet = $this->objWarehouseRal->getWarehouseInfoByDistrictId($arrInput['customer_region_id']);
        if (empty($arrRet)) {
            Order_Exception_Collector::addException($arrInput['stockout_order_id'], 0, '',
                Order_Exception_Const::CONCRETE_WAREHOUSE);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED);
        }
        $arrInput['warehouse_id'] = $arrRet[0]['warehouse_id'];
        $arrInput['warehouse_name'] = $arrRet[0]['warehouse_name'];
        $arrInput['warehouse_location'] = Order_Util_Util::transferBMapToAMap($arrRet[0]['location']);
        return $arrInput;
    }


    /**
     * 校验创建参数
     * @param array $arrInput
     * @return void
     * @throws Order_BusinessError
     */
    public function checkCreateParams($arrInput) {
        if ($arrInput['business_form_order_type']
            && !isset(Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrInput['business_form_order_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR);
        }
        //无人货架信息校验

        $arrShelfInfo = $arrInput['shelf_info'];
        if (empty($arrShelfInfo)) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_SKU_BUSINESS_SHELF_INFO_ERROR);
        }
        if (!isset(Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE[$arrShelfInfo['supply_type']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_SKU_BUSINESS_SHELF_INFO_ERROR);
        }
        if (Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE_CREATE == $arrShelfInfo['supply_type']
            && empty($arrShelfInfo['devices'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_SHELF_ERROR);
        }
        foreach ((array)$arrShelfInfo['devices'] as $intKey => $intAmount) {
            $intKey = intval($intKey);
            $intAmount = intval($intAmount);
            if (!isset(Order_Define_BusinessFormOrder::ORDER_DEVICE_MAP[$intKey])
                || $intAmount <= 0) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_SHELF_ERROR);
            }
        }
        $arrShelfInfo['devices'] = (object)$arrShelfInfo;
        if (count(json_encode($arrShelfInfo)) > 128) {
            Order_BusinessError::throwException(Order_Error_Code::PARAM_ERROR);
        }
        //校验预计送达时间
        $arrExpectArriveTime = $arrInput['expect_arrive_time'];
        $intCurTime = time();
        if ($arrExpectArriveTime['start'] < $intCurTime ||
            $arrExpectArriveTime['end'] <= $arrExpectArriveTime['start']) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_EXPECT_ARRIVE_TIME_ERROR);
        }
        //校验位置信息
        $arrLocation = explode(',', $arrInput['customer_location']);
        if (floatval($arrLocation[1]) >= Order_Define_BusinessFormOrder::MAX_LATITUDE
            || floatval($arrLocation[1]) <= Order_Define_BusinessFormOrder::MIN_LATITUDE) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_LATITUDE_ERROR);
        }
        if (floatval($arrLocation[0]) >= Order_Define_BusinessFormOrder::MAX_LONGITUDE
            || floatval($arrLocation[0]) <= Order_Define_BusinessFormOrder::MIN_LONGITUDE) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_LONGITUDE_ERROR);
        }
        if (!isset(Order_Define_BusinessFormOrder::CUSTOMER_LOCATION_SOURCE_TYPE[$arrInput['customer_location_source']])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_CUSTOMER_LOCATION_SOURCE_ERROR);
        }
    }

    /**
     * 拼接冻结库存参数
     * @param array $arrInput
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
            $arrFreezeStockItem['plan_amount'] = $arrSkuItem['order_amount'];
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
        $arrInput['shelf_info']['devices'] = (object)$arrInput['shelf_info']['devices'];
        $arrCreateParams['business_form_order_id'] = Order_Util_Util::generateBusinessFormOrderId();
        $arrCreateParams['status'] = empty($arrInput['business_form_order_status']) ?
                                        0 : intval($arrInput['business_form_order_status']);
        $arrCreateParams['business_form_order_type'] = empty($arrInput['business_form_order_type']) ?
                                                        0 : intval($arrInput['business_form_order_type']);
        $arrCreateParams['business_form_order_price'] = empty($arrInput['business_form_order_price']) ?
                                                        0 : intval($arrInput['business_form_order_price']);
        $arrCreateParams['business_form_order_remark'] = empty($arrInput['business_form_order_remark']) ? '' : strval($arrInput['business_form_order_remark']);
        $arrCreateParams['customer_id'] = empty($arrInput['customer_id']) ? '' : strval($arrInput['customer_id']);
        $arrCreateParams['customer_name'] = empty($arrInput['customer_name']) ? '' : strval($arrInput['customer_name']);
        $arrCreateParams['customer_contactor'] = empty($arrInput['customer_contactor']) ? '' : strval($arrInput['customer_contactor']);
        $arrCreateParams['customer_contact'] = empty($arrInput['customer_contact']) ? '' : strval($arrInput['customer_contact']);
        $arrCreateParams['customer_address'] = empty($arrInput['customer_address']) ? '' : strval($arrInput['customer_address']);
        $arrCreateParams['customer_city_id'] = empty($arrInput['customer_city_id']) ? 0 : intval($arrInput['customer_city_id']);
        $arrCreateParams['customer_city_name'] = empty($arrInput['customer_city_name']) ? '' : strval($arrInput['customer_city_name']);
        $arrCreateParams['executor'] = empty($arrInput['executor']) ? '' : strval($arrInput['executor']);
        $arrCreateParams['executor_contact'] = empty($arrInput['executor_contact']) ? '' : strval($arrInput['executor_contact']);
        $arrCreateParams['warehouse_id'] = empty($arrInput['warehouse_id']) ? 0 : intval($arrInput['warehouse_id']);
        $arrCreateParams['shelf_info'] = empty($arrInput['shelf_info']) ? '' : json_encode($arrInput['shelf_info']);
        return $arrCreateParams;
    }

    /**
     * 创建业态订单商品参数
     * @param integer $intBusinessFormOrderId
     * @param array $arrSkus
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
            $arrSkuCreateParams['sku_id'] = empty($arrItem['sku_id']) ? 0 : intval($arrItem['sku_id']);
            $arrSkuCreateParams['order_amount'] = empty($arrItem['order_amount']) ? 0 : intval($arrItem['order_amount']);
            $arrSkuCreateParams['distribute_amount'] = empty($arrItem['distribute_amount']) ? 0 : intval($arrItem['distribute_amount']);
            $arrSkuCreateParams['sku_name'] = empty($arrItem['sku_name']) ? '' : strval($arrItem['sku_name']);
            $arrSkuCreateParams['upc_id'] = empty($arrItem['upc_id']) ? '' : strval($arrItem['upc_id']);
            $arrSkuCreateParams['upc_unit'] = empty($arrItem['upc_unit']) ? 0 : intval($arrItem['upc_unit']);
            $arrSkuCreateParams['upc_unit_num'] = empty($arrItem['upc_unit_num']) ? 0 : intval($arrItem['upc_unit_num']);
            $arrSkuCreateParams['send_upc_num'] = empty($arrItem['send_upc_num']) ? 0 : intval($arrItem['send_upc_num']);
            $arrSkuCreateParams['sku_net'] = empty($arrItem['sku_net']) ? '' : strval($arrItem['sku_net']);
            $arrSkuCreateParams['sku_net_unit'] = empty($arrItem['sku_net_unit']) ? 0 : intval($arrItem['sku_net_unit']);
            $arrSkuCreateParams['sku_business_form'] = empty($arrItem['sku_business_form']) ? '' : strval($arrItem['sku_business_form']);
            $arrSkuCreateParams['sku_tax_rate'] = empty($arrItem['sku_tax_rate']) ? 0 : intval($arrItem['sku_tax_rate']);
            $arrSkuCreateParams['cost_price'] = empty($arrItem['cost_price']) ? 0 : intval($arrItem['cost_price']);
            $arrSkuCreateParams['cost_total_price'] = empty($arrItem['cost_total_price']) ? 0 : intval($arrItem['cost_total_price']);
            $arrSkuCreateParams['send_price'] = empty($arrItem['send_price']) ? 0 : intval($arrItem['send_price']);
            $arrSkuCreateParams['send_total_price'] = empty($arrItem['send_total_price']) ? 0 : intval($arrItem['send_total_price']);
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
        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($arrWarehouseIds);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
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
        if (!empty($arrInput['warehouse_ids'])) {
            $arrWareHouseIds = explode(',', $arrInput['warehouse_ids']);
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
            $arrConditions['customer_name'] = $arrInput['customer_name'];
        }
        if (!empty($arrInput['customer_id'])) {
            $arrConditions['customer_id'] = strval($arrInput['customer_id']);
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
        $arrWarehouseList = $this->objWarehouseRal->getWareHouseList($arrBusFormOrderList['warehouse_id']);
        $arrWarehouseList = isset($arrWarehouseList['query_result']) ? $arrWarehouseList['query_result']:[];
        $arrWarehouseList = array_column($arrWarehouseList,null,'warehouse_id');
        $arrBusFormOrderList['warehouse_name'] = isset($arrWarehouseList[$arrBusFormOrderList['warehouse_id']]) ? $arrWarehouseList[$arrBusFormOrderList['warehouse_id']]['warehouse_name'] : '';
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

    /**
     * 根据业态订单id查询业态订单明细
     * @param $arrOrderIds
     * @return array|Model_Orm_BusinessFormOrder
     * @throws
     */
    public function getBusinessFormOrderByIds($arrOrderIds)
    {
        $ret = [];
        if (empty($arrOrderIds)) {
            return $ret;
        }
        $arrBusFormOrderList = Model_Orm_BusinessFormOrder::getBusinessFormOrderByOrderIds($arrOrderIds);
        if (empty($arrBusFormOrderList)) {
            return $ret;
        }

        return $arrBusFormOrderList;
    }
}
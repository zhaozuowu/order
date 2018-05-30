<?php

/**
 * @property int $id
 * @property int $stockin_order_id
 * @property int $stockin_order_type
 * @property int $source_order_id
 * @property string $source_supplier_id
 * @property string $source_info
 * @property int $stockin_order_status
 * @property int $warehouse_id
 * @property string $warehouse_name
 * @property int $stockin_time
 * @property int $reserve_order_plan_time
 * @property int $stockin_order_plan_amount
 * @property int $stockin_order_real_amount
 * @property int $stockin_order_creator_id
 * @property string $stockin_order_creator_name
 * @property string $stockin_order_remark
 * @property int $stockin_order_total_price
 * @property int $stockin_order_total_price_tax
 * @property int $is_delete
 * @property int $create_time
 * @property int $update_time
 * @property int $version
 * @method static Model_Orm_StockinOrder findOne($condition, $orderBy = [], $lockOption = '')
 * @method static Model_Orm_StockinOrder[] findAll($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static Generator|Model_Orm_StockinOrder[] yieldAll($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static Model_Orm_StockinOrder findOneFromRdview($condition, $orderBy = [], $lockOption = '')
 * @method static findRowFromRdview($columns, $condition, $orderBy = [])
 * @method static Model_Orm_StockinOrder[] findAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null, $with = [])
 * @method static findRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static findValueFromRdview($column, $cond, $orderBy = [])
 * @method static findFromRdview($cond = [])
 * @method static findBySqlFromRdview($sql, $asArray = true)
 * @method static countFromRdview($cond, $column = '*')
 * @method static existsFromRdview($cond)
 * @method static Generator|Model_Orm_StockinOrder[] yieldAllFromRdview($cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldRowsFromRdview($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
 * @method static yieldColumnFromRdview($column, $cond, $orderBy = [], $offset = 0, $limit = null)
 */

class Model_Orm_StockinOrder extends Order_Base_Orm
{
    public static $tableName = 'stockin_order';
    public static $dbName = 'nwms_order';
    public static $clusterName = 'nwms_order_cluster';

    /**
     * create stock in order
     * @param int $intStockinOrderId
     * @param int $intStockinOrderType
     * @param int $intStockInOrderDataSourceType
     * @param int $intStockinOrderSource
     * @param int $intSourceOrderId
     * @param int $intStockinBatchId
     * @param string $strSourceSupplierId
     * @param string $strSourceInfo
     * @param int $intStockinOrderStatus
     * @param int $intCityId
     * @param string $strCityName
     * @param int $intWarehouseId
     * @param string $strWarehouseName
     * @param int $intStockinTime
     * @param int $intReserveOrderPlanTime
     * @param int $intStockinOrderPlanAmount
     * @param int $intStockinOrderRealAmount
     * @param int $intStockinOrderCreatorId
     * @param string $strStockinOrderCreatorName
     * @param string $strStockinOrderRemark
     * @param int $intStockinOrderTotalPrice
     * @param  int $intStockinOrderTotalPriceTax
     * @param  int $intCustomerId
     * @param  int $strCustomerName
     * @return int
     */
    public static function createStockinOrder(
        $intStockinOrderId,
        $intStockinOrderType,
        $intStockInOrderDataSourceType,
        $intStockinOrderSource,
        $intSourceOrderId,
        $intStockinBatchId,
        $strSourceSupplierId,
        $strSourceInfo,
        $intStockinOrderStatus,
        $intCityId,
        $strCityName,
        $intWarehouseId,
        $strWarehouseName,
        $intStockinTime,
        $intReserveOrderPlanTime,
        $intStockinOrderPlanAmount,
        $intStockinOrderRealAmount,
        $intStockinOrderCreatorId,
        $strStockinOrderCreatorName,
        $strStockinOrderRemark,
        $intStockinOrderTotalPrice,
        $intStockinOrderTotalPriceTax,
        $intCustomerId,
        $strCustomerName
    )
    {
        $arrRow = [
            'stockin_order_id' => intval($intStockinOrderId),
            'stockin_order_type' => intval($intStockinOrderType),
            'data_source' => intval($intStockInOrderDataSourceType),
            'stockin_order_source' => intval($intStockinOrderSource),
            'source_order_id' => intval($intSourceOrderId),
            'stockin_batch_id' => intval($intStockinBatchId),
            'source_supplier_id' => strval($strSourceSupplierId),
            'source_info' => strval($strSourceInfo),
            'stockin_order_status' => intval($intStockinOrderStatus),
            'city_id' => intval($intCityId),
            'city_name' => strval($strCityName),
            'warehouse_id' => intval($intWarehouseId),
            'warehouse_name' => strval($strWarehouseName),
            'stockin_time' => $intStockinTime,
            'reserve_order_plan_time' => $intReserveOrderPlanTime,
            'stockin_order_plan_amount' => $intStockinOrderPlanAmount,
            'stockin_order_real_amount' => $intStockinOrderRealAmount,
            'stockin_order_creator_id' => $intStockinOrderCreatorId,
            'stockin_order_creator_name' => $strStockinOrderCreatorName,
            'stockin_order_remark' => $strStockinOrderRemark,
            'stockin_order_total_price' => $intStockinOrderTotalPrice,
            'stockin_order_total_price_tax' => $intStockinOrderTotalPriceTax,
            'customer_id' => $intCustomerId,
            'customer_name' => $strCustomerName,
        ];
        return self::insert($arrRow);
    }

    /**
     * @param int $intStockInOrderId 入库单id
     * @param int $intStockInOrderType 入库单类型
     * @param int $intStockInOrderDataSourceType 销退入库单类型
     * @param int $intStockInOrderSource 销退入库单业态
     * @param int $intOrderReturnReason 销退入库原因
     * @param string $strOrderReturnReasonText 销退入库原因
     * @param string $strSourceInfo 来源订单json字符串
     * @param int $intStockinOrderStatus 入库单状态
     * @param int $intCityId 城市id
     * @param string $strCityName 城市名称
     * @param int $intWarehouseId 入库仓库id
     * @param string $strWarehouseName 入库仓库名称
     * @param int $intStockinOrderPlanAmount 计划入库数量
     * @param int $intStockInOrderCreatorId 操作人员id
     * @param string $strStockInOrderCreatorName 操作人员名称
     * @param string $strStockInOrderRemark 备注
     * @param int $intStockinOrderTotalPrice 入库单未税总价格
     * @param int $intStockinOrderTotalPriceTax 入库单含税总价
     * @param int $intShipmentOrderId 运单号
     * @param string $strCustomerId 客户id
     * @param string $strCustomerName 客户名称
     * @param string $strSourceSupplierId 客户id
     * @return int
     */
    public function createRemoveSiteStockInOrder(
        $intStockInOrderId,
        $intStockInOrderType,
        $intStockInOrderDataSourceType,
        $intStockInOrderSource,
        $intOrderReturnReason,
        $strOrderReturnReasonText,
        $strSourceInfo,
        $intStockinOrderStatus,
        $intCityId,
        $strCityName,
        $intWarehouseId,
        $strWarehouseName,
        $intStockinOrderPlanAmount,
        $intStockInOrderCreatorId,
        $strStockInOrderCreatorName,
        $strStockInOrderRemark,
        $intStockinOrderTotalPrice,
        $intStockinOrderTotalPriceTax,
        $intShipmentOrderId,
        $strCustomerId,
        $strCustomerName,
        $strSourceSupplierId,$assetInformation)
    {
        $arrRow = [
            'stockin_order_id' => intval($intStockInOrderId),
            'stockin_order_type' => intval($intStockInOrderType),
            'data_source' => intval($intStockInOrderDataSourceType),
            'stockin_order_source' => intval($intStockInOrderSource),
            'stockin_order_reason' => intval($intOrderReturnReason),
            'stockin_order_reason_text' => strval($strOrderReturnReasonText),
            'shipment_order_id' => intval($intShipmentOrderId),
            'source_info' => strval($strSourceInfo),
            'stockin_order_status' => intval($intStockinOrderStatus),
            'city_id' => intval($intCityId),
            'city_name' => strval($strCityName),
            'warehouse_id' => intval($intWarehouseId),
            'warehouse_name' => strval($strWarehouseName),
            'stockin_order_plan_amount' => $intStockinOrderPlanAmount,
            'stockin_order_creator_id' => $intStockInOrderCreatorId,
            'stockin_order_creator_name' => $strStockInOrderCreatorName,
            'stockin_order_remark' => $strStockInOrderRemark,
            'stockin_order_total_price' => $intStockinOrderTotalPrice,
            'stockin_order_total_price_tax' => $intStockinOrderTotalPriceTax,
            'customer_id' => $strCustomerId,
            'customer_name' => $strCustomerName,
            'source_supplier_id' => $strSourceSupplierId,
            'asset_information' => $assetInformation,
        ];
        return self::insert($arrRow);
    }
    /**
     * @param int $intStockInOrderId 入库单id
     * @param int $intStockInOrderType 入库单类型
     * @param int $intStockInOrderDataSourceType 销退入库单类型
     * @param int $intStockInOrderSource 销退入库单业态
     * @param int $intSourceOrderId 出库单id
     * @param int $intOrderReturnReason 销退入库原因
     * @param string $strOrderReturnReasonText 销退入库原因
     * @param string $strSourceInfo 来源订单json字符串
     * @param int $intStockinOrderStatus 入库单状态
     * @param int $intCityId 城市id
     * @param string $strCityName 城市名称
     * @param int $intWarehouseId 入库仓库id
     * @param string $strWarehouseName 入库仓库名称
     * @param int $intStockinOrderPlanAmount 计划入库数量
     * @param int $intStockInOrderCreatorId 操作人员id
     * @param string $strStockInOrderCreatorName 操作人员名称
     * @param string $strStockInOrderRemark 备注
     * @param int $intStockinOrderTotalPrice 入库单未税总价格
     * @param int $intStockinOrderTotalPriceTax 入库单含税总价
     * @param int $intShipmentOrderId 运单号
     * @param string $strCustomerId 客户id
     * @param string $strCustomerName 客户名称
     * @param string $strSourceSupplierId 客户id
     * @return int
     */
    public static function createStayStockInOrder(
        $intStockInOrderId,
        $intStockInOrderType,
        $intStockInOrderDataSourceType,
        $intStockInOrderSource,
        $intSourceOrderId,
        $intOrderReturnReason,
        $strOrderReturnReasonText,
        $strSourceInfo,
        $intStockinOrderStatus,
        $intCityId,
        $strCityName,
        $intWarehouseId,
        $strWarehouseName,
        $intStockinOrderPlanAmount,
        $intStockInOrderCreatorId,
        $strStockInOrderCreatorName,
        $strStockInOrderRemark,
        $intStockinOrderTotalPrice,
        $intStockinOrderTotalPriceTax,
        $intShipmentOrderId,
        $strCustomerId,
        $strCustomerName,
        $strSourceSupplierId
    )
    {
        $arrRow = [
            'stockin_order_id' => intval($intStockInOrderId),
            'stockin_order_type' => intval($intStockInOrderType),
            'data_source' => intval($intStockInOrderDataSourceType),
            'stockin_order_source' => intval($intStockInOrderSource),
            'source_order_id' => intval($intSourceOrderId),
            'stockin_order_reason' => intval($intOrderReturnReason),
            'stockin_order_reason_text' => strval($strOrderReturnReasonText),
            'shipment_order_id' => intval($intShipmentOrderId),
            'source_info' => strval($strSourceInfo),
            'stockin_order_status' => intval($intStockinOrderStatus),
            'city_id' => intval($intCityId),
            'city_name' => strval($strCityName),
            'warehouse_id' => intval($intWarehouseId),
            'warehouse_name' => strval($strWarehouseName),
            'stockin_order_plan_amount' => $intStockinOrderPlanAmount,
            'stockin_order_creator_id' => $intStockInOrderCreatorId,
            'stockin_order_creator_name' => $strStockInOrderCreatorName,
            'stockin_order_remark' => $strStockInOrderRemark,
            'stockin_order_total_price' => $intStockinOrderTotalPrice,
            'stockin_order_total_price_tax' => $intStockinOrderTotalPriceTax,
            'customer_id' => $strCustomerId,
            'customer_name' => $strCustomerName,
            'source_supplier_id' => $strSourceSupplierId,
        ];
        return self::insert($arrRow);
    }

    /**
     * 获取入库单列表（分页）
     * @param $arrStockinOrderType
     * @param int $intDataSource
     * @param $intStockinOrderId
     * @param $intStockinOrderSourceType
     * @param int $intStockinOrderStatus
     * @param $arrWarehouseId
     * @param $strSourceSupplierId
     * @param $strCustomerName
     * @param $strCustomerId
     * @param $arrSourceOrderIdInfo
     * @param $arrCreateTime
     * @param $arrOrderPlanTime
     * @param $arrStockinTime
     * @param $arrStockinDestroyTime
     * @param $intPrintStatus
     * @param $intPageNum
     * @param $intPageSize
     * @return mixed
     * @throws Order_BusinessError
     */
    public static function getStockinOrderList(
        $arrStockinOrderType,
        $intDataSource,
        $intStockinOrderId,
        $intStockinOrderSourceType,
        $intStockinOrderStatus,
        $arrWarehouseId,
        $strSourceSupplierId,
        $strCustomerName,
        $strCustomerId,
        $arrSourceOrderIdInfo,
        $arrCreateTime,
        $arrOrderPlanTime,
        $arrStockinTime,
        $arrStockinDestroyTime,
        $intPrintStatus,
        $intPageNum,
        $intPageSize
    )

    {
        // 拼装查询条件
        if (!empty($intStockinOrderId)) {
            $arrCondition['stockin_order_id'] = $intStockinOrderId;
        }

        if (!empty($intDataSource)) {
            if(!isset(Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_DEFINE[$intDataSource])){
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKIN_DATA_SOURCE_TYPE_ERROR);
            }
            $arrCondition['data_source'] = $intDataSource;
        }

        if (!empty($arrSourceOrderIdInfo)) {
            $arrCondition['source_order_id'] = $arrSourceOrderIdInfo['source_order_id'];
            // 入库单类型
            $arrCondition['stockin_order_type'] = $arrSourceOrderIdInfo['source_order_type'];
        }

        // 必填入库单参数类型
        if (!empty($arrStockinOrderType)) {
            $arrCondition['stockin_order_type'] = [
                'in',
                $arrStockinOrderType];
        }

        if (!empty($intStockinOrderSourceType)) {
            $arrCondition['stockin_order_source'] = $intStockinOrderSourceType;
        }

        if (!empty($intStockinOrderStatus)) {
            $arrCondition['stockin_order_status'] = $intStockinOrderStatus;
        }

        if (!empty($arrWarehouseId)) {
            $arrCondition['warehouse_id'] = [
                'in',
                $arrWarehouseId];
        }

        if (!empty($strSourceSupplierId)) {
            $arrCondition['source_supplier_id'] = $strSourceSupplierId;
        }

        if (!empty($strCustomerName)) {
            $arrCondition['customer_name'] = [
                'like',
                '%' . $strCustomerName . '%'
            ];
        }

        if (!empty($strCustomerId)) {
            $arrCondition['customer_id'] = [
                'like',
                '%' . $strCustomerId . '%'
            ];
        }

        $intTimesCount = 0;
        if (!empty($arrCreateTime['start'])
            && !empty($arrCreateTime['end'])) {
            $arrCondition['create_time'] = [
                'between',
                $arrCreateTime['start'],
                $arrCreateTime['end']
            ];
            $intTimesCount++;
        }

        if (!empty($arrOrderPlanTime['start'])
            && !empty($arrOrderPlanTime['end'])) {
            $arrCondition['reserve_order_plan_time'] = [
                'between',
                $arrOrderPlanTime['start'],
                $arrOrderPlanTime['end']
            ];
            $intTimesCount++;
        }

        if (!empty($arrStockinTime['start'])
            && !empty($arrStockinTime['end'])) {
            $arrCondition['stockin_time'] = [
                'between',
                $arrStockinTime['start'],
                $arrStockinTime['end'],
            ];
            $intTimesCount++;
        }

        if (!empty($arrStockinDestroyTime['start'])
            && !empty($arrStockinDestroyTime['end'])) {
            $arrCondition['stockin_destroy_time'] = [
                'between',
                $arrStockinDestroyTime['start'],
                $arrStockinDestroyTime['end'],
            ];
            $intTimesCount++;
        }

        if (!empty($intPrintStatus)) {
            $arrCondition['stockin_order_is_print'] = $intPrintStatus;
        }

        // 至少要有一个必传的时间段
        if(1 > $intTimesCount){
            Order_BusinessError::throwException(Order_Error_Code::TIME_PARAMS_LESS_THAN_ONE);
        }

        // 只查询未软删除的
        $arrCondition['is_delete'] = Order_Define_Const::NOT_DELETE;

        // 排序条件
        $orderBy = ['id' => 'desc'];

        // 分页条件
        $offset = (intval($intPageNum) - 1) * intval($intPageSize);
        $limitCount = empty($intPageSize) ? null : intval($intPageSize);


        // 查找满足条件的所有列数据
        $arrCols = self::getAllColumns();

        // 执行一次性查找
        $arrRowsAndTotal = self::findRowsAndTotalCount(
            $arrCols,
            $arrCondition,
            $orderBy,
            $offset,
            $limitCount);

        $arrResult['total'] = $arrRowsAndTotal['total'];
        $arrResult['list'] = $arrRowsAndTotal['rows'];

        return $arrResult;
    }

    /**
     * 查询入库单详情
     *
     * @param $intStockinOrderId
     * @return mixed
     */
    public static function getStockinOrderInfoByStockinOrderId($intStockinOrderId)
    {
        // 只查询未软删除的
        $arrCondition = [
            'is_delete' => Order_Define_Const::NOT_DELETE,
            'stockin_order_id' => $intStockinOrderId,
        ];

        // 查找该行所有数据
        $arrCols = self::getAllColumns();

        // 查找满足条件的所有行数据
        $arrResult = self::findRow($arrCols, $arrCondition);

        return $arrResult;
    }

    /**
     * 对输入的入库单类型进行校验，为空或者不符合返回false
     *
     * @param $arrStockinOrderType
     * @return bool
     */
    public static function isStockinOrderTypeCorrect($arrStockinOrderType)
    {
        // 如果为空返回为错误
        if (empty($arrStockinOrderType)) {
            return false;
        }

        foreach ($arrStockinOrderType as $intType) {
            if (true !== Order_Define_StockinOrder::STOCKIN_ORDER_TYPES[$intType]) {
                return false;
            }
        }

        return true;
    }

    /**
     * 系统销退入库单确认入库
     * @param int    $intStockInOrderId
     * @param int    $intStockInTime
     * @param int    $intStockInOrderRealAmount
     * @param int    $intStockinBatchId
     * @param int    $intRealPriceAmount
     * @param int    $intRealPriceTaxAmount
     * @param string $strRemark
     */
    public static function confirmStockInOrder($intStockInOrderId, $intStockInTime, $intStockInOrderRealAmount, $strRemark, $intStockinBatchId, $intRealPriceAmount, $intRealPriceTaxAmount)
    {
        $arrCondition = [
            'stockin_order_id' => $intStockInOrderId,
        ];
        $arrUpdateInfo = [
            'stockin_time' => $intStockInTime,
            'stockin_order_remark' => $strRemark,
            'stockin_order_real_amount' => $intStockInOrderRealAmount,
            'stockin_order_total_price' => $intRealPriceAmount,
            'stockin_order_total_price_tax' => $intRealPriceTaxAmount,
            'stockin_batch_id' => $intStockinBatchId,
            'stockin_order_status' => Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH,
        ];
        self::findOne($arrCondition)->update($arrUpdateInfo);
    }

    /**
     * 通过source order id 获取入库单详情
     * @param $intSourceOrderId
     * @return array
     */
    public static function getStockInOrderInfoBySourceOrderId($intSourceOrderId)
    {
        $objStockInOrder = self::findOne([
            'source_order_id' => $intSourceOrderId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ]);
        if (!empty($objStockInOrder)) {
            return $objStockInOrder->toArray();
        }
        return [];
    }

    /**
     * 批量查询入库单详情
     *
     * @param $arrStockinOrderIds
     * @return mixed
     */
    public static function getStockinOrderInfoByStockinOrderIds($arrStockinOrderIds)
    {
        // 只查询未软删除的
        $arrCondition = [
            'is_delete' => Order_Define_Const::NOT_DELETE,
            'stockin_order_id' => [
                'in',
                $arrStockinOrderIds,
            ],
        ];

        // 查找该行所有数据
        $arrCols = self::getAllColumns();

        // 查找满足条件的所有行数据
        $arrResult = self::findRows($arrCols, $arrCondition);
        return $arrResult;
    }

}

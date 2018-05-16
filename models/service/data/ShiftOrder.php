<?php
/**
 * @name    Service_Data_StockAdjustOrder
 * @desc 库存调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_ShiftOrder
{
    /**
     * @var Dao_Ral_Stock
     */
//    protected $objDaoShiftOrder;

    /**
     * init
     */
    public function __construct()
    {
//        $this->objDaoShiftOrder = new Dao_Ral_Stock();
    }

    /**
     * 新建调整单
     * @param $arrInput
     * @return arrayShift
     */
    public function createShiftOrder($arrInput)
    {
        $arrOrderArg       = $this->getCreateOrderArg($arrInput);
        $arrOrderDetailArg = $this->getCreateOrderDetailArg($arrInput);
        Bd_Log::trace('insert into shift order ' . json_encode($arrOrderArg));
        Bd_Log::trace('insert into shift order detail ' . json_encode($arrOrderDetailArg));

        // 新建调整单和调整单详情
        return Model_Orm_ShiftOrder::getConnection()->transaction(function () use ($arrOrderArg, $arrOrderDetailArg) {
            Model_Orm_ShiftOrder::insert($arrOrderArg);
            Model_Orm_ShiftOrderDetail::batchInsert($arrOrderDetailArg);
            return ['shift_order_id' => $arrOrderArg['shift_order_id']];
        });
        Bd_Log::trace('create shift order return ' . print_r($arrRet, true));
    }

    /**
     * 新建调整单
     * @param $arrInput
     * @return arrayShift
     */
    public function cancelShiftOrder($arrInput)
    {
        $arrRet = [];
        $arrRet = $this->insert($arrOrderArg, $arrOrderDetailArg);

        Bd_Log::trace('create shift order return ' . print_r($arrRet, true));
        return $arrRet;
    }


    /**
     * 获取商品库存信息
     * @param $intWarehouseId
     * @param $arrSkuIds
     * @return array
     */
    protected function getSkuStocks($intWarehouseId, $arrSkuIds)
    {
        $arrStocks = $this->objDaoStock->getStockInfo($intWarehouseId, $arrSkuIds);
        if (empty($arrStocks)) {
            Bd_Log::warning(__METHOD__ . ' get sku stock failed. call ral failed.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL);
        }

        return Order_Util_Util::arrayToKeyValue($arrStocks, 'sku_id');
    }

    /**
     * 获取sku详情
     * @param array $arrSkuIds sku id 数组
     * @return array
     */
    protected function getSkuInfos($arrSkuIds)
    {
        if (empty($arrSkuIds)) {
            return [];
        }
        $arrSkuIds = array_unique($arrSkuIds);

        $arrSkuInfos = $this->objDaoSku->getSkuInfos($arrSkuIds);

        if (empty($arrSkuInfos)) {
            Bd_Log::warning(__METHOD__ . ' get sku info failed. call ral failed.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED);
        }

        return $arrSkuInfos;
    }

    /**
     * 查询调整单
     * 如果传入page_size，则分页返回结果，否则不分页。
     * @param $arrInput
     * @return array
     */
    public function get($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrColumns    = Model_Orm_StockAdjustOrder::getAllColumns();
        $arrConditions = $this->getConditions($arrInput);
        $arrOrderBy    = ['warehouse_id' => 'asc', 'id' => 'desc'];

        $intOffset = 0;
        $intLimit  = null;

        if (!empty($arrInput['page_size'])) {
            if (empty($arrInput['page_num'])) {
                $arrInput['page_num'] = 1;
            }
            $intOffset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
            $intLimit  = $arrInput['page_size'];
        }

        $arrRet = Model_Orm_StockAdjustOrder::findRows($arrColumns, $arrConditions, $arrOrderBy, $intOffset, $intLimit);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        return $arrRet;
    }

    /**
     * 获取符合条件的调整单总行数
     * @param $arrInput
     * @return int
     */
    public function getCount($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrConditions = $this->getConditions($arrInput);
        $ret           = Model_Orm_StockAdjustOrder::count($arrConditions);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . $ret);
        return $ret;
    }

    /**
     * 获取查询条件
     * @param $arrInput
     * @return array
     */
    protected function getConditions($arrInput)
    {
        $arrFormatInput = [
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        if (!empty($arrInput['warehouse_ids'])) {
            $arrFormatInput['warehouse_id'] = ['in', $arrInput['warehouse_ids']];
        }
        if (!empty($arrInput['warehouse_id'])) {
            $arrFormatInput['warehouse_id'] = $arrInput['warehouse_id'];
        }
        if (!empty($arrInput['stock_adjust_order_id'])) {
            $arrFormatInput['stock_adjust_order_id'] = $arrInput['stock_adjust_order_id'];
        }
        if (!empty($arrInput['stock_adjust_order_ids'])) {
            $arrFormatInput['stock_adjust_order_id'] = ['in', $arrInput['stock_adjust_order_ids']];
        }
        if (!empty($arrInput['adjust_type'])) {
            $strAdjustType = Nscm_Define_Stock::ADJUST_TYPE_MAP[$arrInput['adjust_type']];
            if (empty($strAdjustType)) {
                Bd_Log::warning('adjust type invalid ', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
            $arrFormatInput['adjust_type'] = $arrInput['adjust_type'];
        }

        if (!empty($arrInput['begin_date'])) {
            $arrFormatInput['create_time'][] = ['>=', $arrInput['begin_date']];
        }

        if (!empty($arrInput['end_date'])) {
            $arrFormatInput['create_time'][] = ['<=', $arrInput['end_date']];
        }

        return $arrFormatInput;
    }

    /**
     * 检查、拼装新建调整单参数
     * @param $arrInput
     * @return array
     */
    protected function getCreateOrderArg($arrInput)
    {

        $intTotalAmount = 0;
        $intTotalKinds = 0;
        $skuList = array();

        foreach ($arrInput['detail'] as $arrSkuDetail) {
            $intTotalAmount += $arrSkuDetail['shift_amount'];
            if(isset($skuList[$arrSkuDetail['sku_id']])) continue;
            $skuList[$arrSkuDetail['sku_id']] = $arrSkuDetail['sku_name'];
            $intTotalKinds++;
        }


        $intCreator     = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
        $strCreatorName = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];

        if (empty($intCreator) || empty($strCreatorName)) {
            Bd_Log::warning('get user info failed ', Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR);
        }

        $arrOrderArg = [
            'shift_order_id'    => $arrInput['shift_order_id'],
            'warehouse_id'      => $arrInput['warehouse_id'],
            'sku_kinds'         => $intTotalKinds,
            'sku_amount'        => $intTotalAmount,
            'status'            => Order_Define_ShiftOrder::SHIFT_ORDER_STATUS_CREATE,
            'source_location'   => $arrInput['source_location'],
            'target_location'   => $arrInput['target_location'],
            'detail'            => json_encode($skuList),
            'creator'           => $intCreator,
            'creator_name'      => $strCreatorName,
        ];

        return $arrOrderArg;
    }

    /**
     * 检查、拼装新建调整单详情参数
     * @param $arrInput
     * @param $arrSkuInfos
     * @param $arrStockInfos
     * @return array
     */
    protected function getCreateOrderDetailArg($arrInput)
    {
        $arrOrderDetailArg = [];
        foreach ($arrInput['detail'] as $arrDetail) {

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetailItem = [
                'shift_order_id'        => $arrInput['shift_order_id'],
                'warehouse_id'          => $arrInput['warehouse_id'],
                'sku_id'                => $arrDetail['sku_id'],
                'sku_name'              => $arrDetail['sku_name'],
                'shift_amount'          => $arrDetail['shift_amount'],
                'is_defective'          => $arrDetail['is_defective'],
                'upc_id'                => $arrDetail['upc_id'],
                'upc_unit'              => $arrDetail['upc_unit'],
                'upc_unit_num'          => $arrDetail['upc_unit_num'],
                'production_time'       => $arrDetail['production_time'],
                'expire_time'           => $arrDetail['expire_time'],
            ];

            $arrOrderDetailArg[] = $arrDetailItem;
        }

        return $arrOrderDetailArg;
    }


    /**
     * 获取移位单详情
     * @param $stock_adjust_order_id
     * @return Model_Orm_StockAdjustOrder
     */
    public static function getByOrderId($shift_order_id)
    {
        if(empty($stock_adjust_order_id)) {
            Bd_Log::warning('stock shift order id invalid', Order_Error_Code::PARAMS_ERROR, $shift_order_id);
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        $arrConditions = [
            'is_delete'             => Order_Define_Const::NOT_DELETE,
            'shift_order_id' => $shift_order_id,
        ];

        // 获取所有字段
        $arrColumns = self::getAllColumns();

        $arrRet = Model_Orm_ShiftOrder::findRow($arrColumns, $arrConditions);

        Bd_Log::debug(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        return $arrRet;
    }





}
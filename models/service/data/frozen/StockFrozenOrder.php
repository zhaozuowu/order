<?php
/**
 * @name Service_Data_Frozen_StockFrozenOrder
 * @desc 冻结单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_Frozen_StockFrozenOrder
{
    /**
     * @var Dao_Ral_Stock
     */
    protected $objDaoStock;

    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoSku = new Dao_Ral_Sku();
        $this->objDaoStock = new Dao_Ral_Stock();
    }

    /**
     * 新建冻结单
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function createFrozenOrder($arrInput) {
        $arrRet = [];
        // 获取SKU详情
        $arrSkuIds = array_unique(array_column($arrInput['detail'], 'sku_id'));

        if(count($arrSkuIds) > Order_Define_StockFrozenOrder::STOCK_FROZEN_ORDER_MAX_SKU) {
            Bd_Log::warning('frozen order exceed the maximum number of sku amount', Order_Error_Code::NWMS_ORDER_FROZEN_SKU_AMOUNT_TOO_MUCH, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_FROZEN_SKU_AMOUNT_TOO_MUCH);
        }

        $arrSkuInfos = $this->getSkuInfos($arrSkuIds);

        // 参数检查、组合数据库需要字段
        $arrOrderArg = $this->getCreateOrderArg($arrInput);
        $arrOrderDetailArg = $this->getCreateOrderDetailArg($arrInput, $arrSkuInfos);

        // 调用库存模块，新增批次库存
        $this->frozenStock($arrInput, $arrSkuInfos);

        $arrRet = $this->insert($arrOrderArg, $arrOrderDetailArg);

        Bd_Log::trace('create stock frozen order return ' . print_r($arrRet, true));
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

        $arrSkuInfos = $this->objDaoSku->getSkuInfos($arrSkuIds);

        if(empty($arrSkuInfos)) {
            Bd_Log::warning(__METHOD__ . ' get sku info failed. call ral failed.');
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED);
        }

        return $arrSkuInfos;
    }

    /**
     * 写入数据库
     * @param $arrOrderArg
     * @param $arrOrderDetailArg
     * @return bool|mixed
     * @throws Exception
     */
    public function insert($arrOrderArg, $arrOrderDetailArg) {
        Bd_Log::trace('insert into stock frozen order ' . json_encode($arrOrderArg));
        Bd_Log::trace('insert into stock frozen order detail ' . json_encode($arrOrderDetailArg));

        return Model_Orm_StockFrozenOrder::getConnection()->transaction(function () use ($arrOrderArg, $arrOrderDetailArg) {
            Model_Orm_StockFrozenOrder::insert($arrOrderArg);
            Model_Orm_StockFrozenOrderDetail::batchInsert($arrOrderDetailArg);
            return ['stock_frozen_order_id' => $arrOrderArg['stock_frozen_order_id']];
        });
    }

    /**
     * 查询冻结单
     * 如果传入page_size，则分页返回结果，否则不分页。
     * @param $arrInput
     * @return array
     */
    public function getOrderList($arrInput) {
        Bd_Log::trace(__METHOD__ . '  param ', 0, $arrInput);

        $arrSql = $this->buildGetOrderListSql($arrInput);

        $arrRet = Model_Orm_StockFrozenOrder::findRows($arrSql['columns'], $arrSql['where'],
            $arrSql['order_by'], $arrSql['offset'], $arrSql['limit']);
        Bd_Log::trace(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        return $arrRet;
    }

    /**
     * 查询冻结单个数
     * @param $arrInput
     * @return int
     */
    public function getOrderListCount($arrInput) {
        Bd_Log::trace(__METHOD__ . '  param ', 0, $arrInput);

        $arrSql = $this->buildGetOrderListSql($arrInput);
        $ret = Model_Orm_StockFrozenOrder::count( $arrSql['where']);

        Bd_Log::trace(__METHOD__ . 'sql return: ' . $ret);
        return $ret;
    }

    /**
     * 拼装冻结单查询条件
     * @param $arrInput
     * @return array
     */
    protected function buildGetOrderListSql($arrInput) {
        $arrSql = [];

        $intOffset = 0;
        $intLimit = null;

        if(!empty($arrInput['page_size'])) {
            if(empty($arrInput['page_num'])) {
                $arrInput['page_num'] = 1;
            }
            $intOffset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
            $intLimit = $arrInput['page_size'];
        }


        $arrWhere = [
            'is_delete'     => Order_Define_Const::NOT_DELETE,
        ];
        if(!empty($arrInput['warehouse_ids'])) {
            $arrWhere['warehouse_id'] = ['in', $arrInput['warehouse_ids']];
        }
        if(!empty($arrInput['stock_frozen_order_id'])) {
            $arrWhere['stock_frozen_order_id'] = $arrInput['stock_frozen_order_id'];
        }
        if(!empty($arrInput['create_type'])) {
            $arrWhere['create_type'] = $arrInput['create_type'];
        }
        if(!empty($arrInput['order_status'])) {
            $arrWhere['order_status'] = $arrInput['order_status'];
        }

        if(!empty($arrInput['create_time_start'])) {
            $arrWhere['create_time'][] = ['>=', $arrInput['create_time_start']];
        }

        if(!empty($arrInput['create_time_end'])) {
            $arrWhere['create_time'][] = ['<=', $arrInput['create_time_end']];
        }

        if(!empty($arrInput['close_time_start'])) {
            $arrWhere['close_time'][] = ['>=', $arrInput['create_time_start']];
        }

        if(!empty($arrInput['close_time_end'])) {
            $arrWhere['close_time'][] = ['<=', $arrInput['create_time_end']];
        }

        $arrSql['columns'] = Model_Orm_StockFrozenOrder::getAllColumns();
        $arrSql['order_by'] = ['warehouse_id' => 'asc', 'id' => 'desc'];
        $arrSql['limit'] = $intLimit;
        $arrSql['offset'] = $intOffset;
        $arrSql['where'] = $arrWhere;
        return $arrSql;
    }

    /**
     * 拼装新建冻结单参数
     * @param $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    protected function getCreateOrderArg($arrInput)
    {
        $intOriginTotalFrozenAmount = 0;
        foreach ($arrInput['detail'] as $arrDetail) {
            $intOriginTotalFrozenAmount += $arrDetail['adjust_amount'];
        }
        if($intOriginTotalFrozenAmount <= 0) {
            Bd_Log::warning('frozen amount invalid ', Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR);
        }

        $arrSkuIds = array_values(array_unique(array_column($arrInput['detail'], 'sku_id')));
        $intSkuAmount = count($arrSkuIds);
        if($intSkuAmount <= 0) {
            Bd_Log::warning('frozen sku amount invalid ', Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR);
        }

        $intCreator = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
        $strCreatorName = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];

        $arrOrderArg = [
            'stock_frozen_order_id'         => $arrInput['stock_frozen_order_id'],
            'warehouse_id'                  => $arrInput['warehouse_id'],
            'warehouse_name'                => $arrInput['warehouse_name'],
            'order_status'                  => Order_Define_StockFrozenOrder::FROZEN_ORDER_STATUS_FROZEN,
            'origin_total_frozen_amount'    => $intOriginTotalFrozenAmount,
            'current_total_frozen_amount'   => $intOriginTotalFrozenAmount,
            'sku_amount'                    => $intSkuAmount,
            'remark'                        => $arrInput['remark'],
            'create_type'                   => Order_Define_StockFrozenOrder::FROZEN_ORDER_CREATE_BY_USER,
            'creator'                       => $intCreator,
            'creator_name'                  => $strCreatorName,
            'close_user_id'                 => 0,
            'close_user_name'               => '',
            'close_time'                    => 0,
        ];

        return $arrOrderArg;
    }

    /**
     * 创建冻结单明细参数
     * @param $arrInput
     * @param $arrSkuInfos
     * @return array
     * @throws Order_BusinessError
     */
    protected function getCreateOrderDetailArg($arrInput, $arrSkuInfos)
    {
        $arrOrderDetailArg = [];
        foreach ($arrInput['detail'] as $arrDetail) {
            if(intval($arrDetail['frozen_amount']) <= 0) {
                Bd_Log::warning('frozen amount invalid in detail param', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSkuInfo = $arrSkuInfos[$arrDetail['sku_id']];
            if(empty($arrSkuInfo)) {
                Bd_Log::warning("sku info is empty. sku id: " . $arrDetail['sku_id'], Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR);
            }

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetail = $this->getEffectTime($arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);

            $arrDetail = [
                'stock_frozen_order_id'     => $arrInput['stock_frozen_order_id'],
                'warehouse_id'              => $arrInput['warehouse_id'],
                'sku_id'                    => $arrDetail['sku_id'],
                'upc_id'                    => $arrSkuInfo['min_upc']['upc_id'],
                'sku_name'                  => $arrSkuInfo['sku_name'],
                'storage_location_id'       => $arrDetail['storage_location_id'],
                'origin_frozen_amount'      => $arrDetail['frozen_amount'],
                'current_frozen_amount'     => $arrDetail['frozen_amount'],
                'is_defective'              => $arrInput['is_defective'],
                'production_time'           => $arrDetail['production_time'],
                'expire_time'               => $arrDetail['expire_time'],
            ];

            $arrOrderDetailArg[] = $arrDetail;
        }

        return $arrOrderDetailArg;
    }

    /**
     * 调用stock模块，冻结库存
     * @param $arrStockOut
     * @return array
     */
    protected function frozenStock($arrInput, $arrSkuInfos)
    {
        $arrRet = [];
        $arrStockFrozenArg = $this->getStockFrozenArg($arrInput, $arrSkuInfos);
        Bd_Log::trace('ral call stock frozen param: ' . print_r($arrStockFrozenArg, true));

        $arrRet =  $this->objDaoStock->frozenStock($arrStockFrozenArg);

        Bd_Log::trace('ral call stock frozen return:  ' . print_r($arrRet,true));
    }

    /**
     * 根据商品效期类型，计算生产日期和有效期
     * 计算结果返回到$arrDetail['production_time'] 和 $arrDetail['expire_time']
     * @param $arrDetail
     * @param $intSkuEffectType
     * @param $intSkuEffectDay
     * @return mixed
     */
    public function getEffectTime($arrDetail, $intSkuEffectType, $intSkuEffectDay)
    {
        if(empty($intSkuEffectType)) {
            Bd_Log::warning('sku effect type is empty ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        $intSkuEffectType = intval($intSkuEffectType);

        // 如果是生产日期型的，有效期天数必传
        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            if(!is_numeric($intSkuEffectDay) || $intSkuEffectDay < 0) {
                Bd_Log::warning('sku effect day invalid ' . $intSkuEffectDay);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
            }
        }

        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            // 生产日期型
            $arrDetail['production_time'] = $arrDetail['production_or_expire_time'];
            $arrDetail['expire_time'] = $arrDetail['production_or_expire_time'] + $intSkuEffectDay * 3600 * 24;
        } else if(Nscm_Define_Sku::SKU_EFFECT_TO === $intSkuEffectType) {
            // 到效期型
            $arrDetail['production_time'] = '';
            $arrDetail['expire_time'] = $arrDetail['production_or_expire_time'];
        } else {
            Bd_Log::warning('sku effect type invalid ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        return $arrDetail;
    }

    protected function getStockFrozenArg($arrInput, $arrSkuInfos)
    {
        $arrStockFrozenArg = [
            'ext_order_id'      => $arrInput['stock_frozen_order_id'],
            'warehouse_id'          => $arrInput['warehouse_id'],
            'details'      => [],
        ];

        foreach ($arrInput['detail'] as $arrDetail) {
            $intSkuId = $arrDetail['sku_id'];
            $arrSkuInfo = $arrSkuInfos[$intSkuId];

            if(empty($arrSkuInfo)) {
                Bd_Log::warning("sku info is empty. sku id: " . $arrDetail['sku_id'], Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR);
            }

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetail = $this->getEffectTime(
                $arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);

            $arrFrozenInfo = [
                'freeze_amount' => $arrDetail['freeze_amount'],
                'is_defective' => $arrDetail['is_defective'],
                'expiration_time' => $arrDetail['expire_time'],
            ];

            $arrStockFrozenArg['details'][$intSkuId]['sku_id'] = $intSkuId;
            $arrStockFrozenArg['details'][$intSkuId]['freeze_info'][] = $arrFrozenInfo;
        }

        return $arrStockFrozenArg;
    }
}
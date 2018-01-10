<?php
/**
 * @name    Service_Data_StockAdjustOrder
 * @desc 库存调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_StockAdjustOrder
{
    /**
     * 新建调整单
     * @param $arrInput
     * @return array
     */
    public function createAdjustOrder($arrInput)
    {
        $arrRet = [];
        // 获取SKU详情
        $arrSkuIds = array_unique(array_column($arrInput['detail'], 'sku_id'));
        $arrSkuInfos = $this->getSkuInfos($arrSkuIds);

        // 参数检查、组合数据库需要字段
        $arrOrderArg = $this->getCreateOrderArg($arrInput);
        $arrOrderDetailArg = $this->getCreateOrderDetailArg($arrInput, $arrSkuInfos);

        // 调用库存模块，新增批次库存
        $this->adjustStock($arrInput, $arrSkuInfos);

        $arrRet = $this->insert($arrOrderArg, $arrOrderDetailArg);
        return $arrRet;
    }

    /**
     * 查询采购单详情
     * @param $stock_adjust_order_id
     * @return Model_Orm_StockAdjustOrder
     */
    public function getByOrderId($stock_adjust_order_id)
    {
        return Model_Orm_StockAdjustOrder::getByOrderId($stock_adjust_order_id);
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
        //todo 等待接口
        return [
            '1000001' => [
                'sku_id' => 1000001,
                'sku_name' => 'skuname1',
                'sku_category_1_name' => '水果1',
                'sku_category_2_name' => '水果2',
                'sku_category_3_name' => '苹果',
                'sku_from_country' => '进口',
                'sku_effect_alarm_day' => 5,
                'sku_effect_type' => 1,
                'sku_effect_day' => 1000,
                'upc_id' => 1000,
                'upc_unit' => 1,
                'upc_unit_num' => 1000,
                'sku_net' => 1000,
                'sku_net_unit' => 1,
            ],
            '1000002' => [
                'sku_id' => 1000002,
                'sku_name' => 'skuname2',
                'sku_category_1_name' => '饮料1',
                'sku_category_2_name' => '饮料2',
                'sku_category_3_name' => '矿泉水',
                'sku_from_country' => '国产',
                'sku_effect_alarm_day' => 10,
                'sku_effect_type' => 2,
                'sku_effect_day' => 0,
                'upc_id' => 1000,
                'upc_unit' => 2,
                'upc_unit_num' => 1000,
                'sku_net' => 1000,
                'sku_net_unit' => 2,
            ],
        ];
    }

    /**
     * 写入数据库
     * @param $arrOrderArg
     * @param $arrOrderDetailArg
     * @return array
     */
    public function insert($arrOrderArg, $arrOrderDetailArg)
    {
        Bd_Log::debug('新建调整单' . print_r($arrOrderArg, true));
        Bd_Log::debug('新建调整单详情' . print_r($arrOrderDetailArg, true));

        // 新建调整单和调整单详情
        return Model_Orm_StockAdjustOrder::getConnection()->transaction(function () use ($arrOrderArg, $arrOrderDetailArg) {
            Model_Orm_StockAdjustOrder::insert($arrOrderArg);
            Model_Orm_StockAdjustOrderDetail::batchInsert($arrOrderDetailArg);
            return ['stock_adjust_order_id' => $arrOrderArg['stock_adjust_order_id']];
        });
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

        $arrColumns = Model_Orm_StockAdjustOrder::getAllColumns();
        $arrConditions = $this->getConditions($arrInput);
        $arrOrderBy = ['warehouse_id' => 'asc', 'create_time' => 'desc'];

        $intOffset = 0;
        $intLimit = null;

        if(!empty($arrInput['page_size'])) {
            if(empty($arrInput['page_num'])) {
                $arrInput['page_num'] = 1;
            }
            $intOffset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
            $intLimit = $arrInput['page_size'];
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
    public function getCount($arrInput) {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrConditions = $this->getConditions($arrInput);
        $ret = Model_Orm_StockAdjustOrder::count( $arrConditions);
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
            'is_delete'     => Order_Define_Const::NOT_DELETE,
        ];
        if(!empty($arrInput['warehouse_ids'])) {
            $arrFormatInput['warehouse_id'] = ['in', $arrInput['warehouse_ids']];
        }
        if(!empty($arrInput['warehouse_id'])) {
            $arrFormatInput['warehouse_id'] = $arrInput['warehouse_id'];
        }
        if(!empty($arrInput['stock_adjust_order_id'])) {
            $arrFormatInput['stock_adjust_order_id'] = $arrInput['stock_adjust_order_id'];
        }
        if(!empty($arrInput['stock_adjust_order_ids'])) {
            $arrFormatInput['stock_adjust_order_id'] = ['in', $arrInput['stock_adjust_order_ids']];
        }
        if(!empty($arrInput['adjust_type'])) {
            $strAdjustType = Nscm_Define_Stock::ADJUST_TYPE_MAP[$arrInput['adjust_type']];
            if(empty($strAdjustType)) {
                Bd_Log::warning('调整单类型不正确', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
            $arrFormatInput['adjust_type'] = $arrInput['adjust_type'];
        }

        //todo 是否需要判断最大查询时间间隔
        if(!empty($arrInput['begin_date'])) {
            $arrFormatInput['create_time'][] = ['>=', $arrInput['begin_date']];
        }

        if(!empty($arrInput['end_date'])) {
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
        $strAdjustType = Nscm_Define_Stock::ADJUST_TYPE_MAP[$arrInput['adjust_type']];
        if(empty($strAdjustType)) {
            Bd_Log::warning('调整单类型不正确', Order_Error_Code::PARAMS_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        $intTotalAmount = 0;
        foreach ($arrInput['detail'] as $arrDetail) {
            $intTotalAmount += $arrDetail['adjust_amount'];
        }

        if($intTotalAmount <= 0) {
            Bd_Log::warning('调整单数量不正确', Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR);
        }

        $intCreator = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
        $strCreatorName = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];
        if(empty($intCreator) || empty($strCreatorName)) {
            Bd_Log::warning('获取用户信息失败', Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR, $arrInput);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR);
        }

        $arrOrderArg = [
            'stock_adjust_order_id' => $arrInput['stock_adjust_order_id'],
            'warehouse_id'          => $arrInput['warehouse_id'],
            'warehouse_name'        => $arrInput['warehouse_name'],
            'total_adjust_amount'   => $intTotalAmount,
            'adjust_type'           => $arrInput['adjust_type'],
            'remark'                => $arrInput['remark'],
            'creator'               => '123456',
            'creator_name'          => 'szx'
            //'creator'               => $intCreator,
            //'creator_name'          => $strCreatorName,
        ];

        return $arrOrderArg;
    }

    /**
     * 检查、拼装新建调整单详情参数
     * @param $arrInput
     * @param $arrSkuInfos
     * @return array
     */
    protected function getCreateOrderDetailArg($arrInput, $arrSkuInfos)
    {
        $arrOrderDetailArg = [];
        foreach ($arrInput['detail'] as $arrDetail) {
            $arrDetail['adjust_type'] = $arrInput['adjust_type'];
            $strAdjustType = Nscm_Define_Stock::ADJUST_TYPE_MAP[$arrDetail['adjust_type']];
            if(empty($strAdjustType)) {
                Bd_Log::warning('detail中调整单类型不正确', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            if(intval($arrDetail['adjust_amount']) <= 0) {
                Bd_Log::warning('detail中调整数量不正确', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }


            $arrSkuInfo = $arrSkuInfos[$arrDetail['sku_id']];
            if(empty($arrSkuInfo)) {
                Bd_Log::warning("sku id不存在" . $arrDetail['sku_id'], Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR);
            }

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetail = $this->getEffectTime($arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);

            $arrDetail = [
                'stock_adjust_order_id'     => $arrInput['stock_adjust_order_id'],
                'warehouse_id'              => $arrInput['warehouse_id'],
                'adjust_type'               => $arrInput['adjust_type'],
                'sku_id'                    => $arrDetail['sku_id'],
                'sku_name'                  => $arrSkuInfo['sku_name'],
                'adjust_amount'             => $arrDetail['adjust_amount'],
                'upc_id'                    => $arrSkuInfo['upc_id'],
                'upc_unit'                  => $arrSkuInfo['upc_unit'],
                'upc_unit_num'              => $arrSkuInfo['upc_unit_num'],
                'sku_net'                   => $arrSkuInfo['sku_net'],
                'sku_net_unit'              => $arrSkuInfo['sku_net_unit'],
                'unit_price'                => $arrDetail['unit_price'],
                'unit_price_tax'            => $arrDetail['unit_price_tax'],
                'production_time'           => $arrDetail['production_time'],
                'expire_time'               => $arrDetail['expire_time'],
            ];

            $arrOrderDetailArg[] = $arrDetail;
        }

        return $arrOrderDetailArg;
    }


    /**
     * 库存调整
     * @param $arrInput
     * @param $arrSkuInfos
     * @return bool
     */
    protected function adjustStock($arrInput, $arrSkuInfos)
    {
        if(empty($arrInput['adjust_type'])) {
            Bd_Log::warning(__METHOD__ . ' 库存调整类型不正确 '. print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_TYPE_ERROR);
        }

        $strStockType = Nscm_Define_Stock::ADJUST_TYPE_MAP[$arrInput['adjust_type']];
        if(empty($strStockType)) {
            Bd_Log::warning(__METHOD__ . ' 库存调整类型不正确 '. print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_TYPE_ERROR);
        }

        $intAdjustType = intval($arrInput['adjust_type']);
        if(in_array($intAdjustType, Nscm_Define_Stock::STOCK_IN_TYPE)) {
            //调增
            $arrStockIn = $this->getCreateBatchStockInArg($arrInput, $arrSkuInfos);
            $batchStockRet = $this->createBatchStockIn($arrStockIn);
        } else if(in_array($intAdjustType, Nscm_Define_Stock::STOCK_OUT_TYPE)) {
            //调减
            $arrStockOut = $this->getCreateBatchStockOutArg($arrInput);
            $batchStockRet = $this->createBatchStockOut($arrStockOut);
        } else {
            Bd_Log::warning(__METHOD__ . ' 库存调整类型不正确 '. print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_TYPE_ERROR);
        }

        return true;
    }


    /**
     * 拼装批次入库参数
     * @param $arrInput
     * @param $arrSkuInfos
     * @return array
     */
    protected function getCreateBatchStockInArg($arrInput, $arrSkuInfos)
    {
        $arrStockIn = [
            'stockin_order_id'      => $arrInput['stock_adjust_order_id'],
            'stockin_order_type'    => $arrInput['adjust_type'],
            'warehouse_id'          => $arrInput['warehouse_id'],
            'stockin_sku_info'      => [],
        ];

        foreach ($arrInput['detail'] as $arrDetail) {
            $intSkuId = $arrDetail['sku_id'];
            $arrSkuInfo = $arrSkuInfos[$intSkuId];

            if(empty($arrSkuInfo)) {
                Bd_Log::warning("无效的sku id" . $arrDetail['sku_id'], Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR);
            }

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetail = $this->getEffectTime(
                $arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);

            $arrStockSkuInfo = [
                'sku_id'            => $arrDetail['sku_id'],
                'unit_price'        => $arrDetail['unit_price'],
                'unit_price_tax'    => $arrDetail['unit_price_tax'],
                'batch_info'        => [
                    [
                        'expire_time'           =>  $arrDetail['expire_time'],
                        'production_time'       =>  $arrDetail['production_time'],
                        'amount'                =>  $arrDetail['adjust_amount'],
                    ],
                ],
            ];

            $arrStockIn['stockin_sku_info'][] = $arrStockSkuInfo;
        }

        return $arrStockIn;
    }

    /**
     * 库存调增
     * @param $arrStockIn
     * @return mixed
     */
    protected function createBatchStockIn($arrStockIn)
    {
        $arrRet = [];
        Bd_Log::debug('调用库存模块参数 ' . print_r($arrStockIn,true));
        $arrRet =  Nscm_Service_Stock::stockin($arrStockIn);
        Bd_Log::debug('调用库存模块返回值 ' . print_r($arrRet,true));
        return $arrRet;
    }


    /**
     * 拼装出库参数
     * @param $arrInput
     * @return array
     */
    protected function getCreateBatchStockOutArg($arrInput)
    {
        $arrStockOut = [
            'stockin_order_id'      => $arrInput['stock_adjust_order_id'],
            'inventory_type'        => $arrInput['adjust_type'],
            'warehouse_id'          => $arrInput['warehouse_id'],
            'stockout_details'      => [],
        ];

        foreach ($arrInput['detail'] as $arrDetail) {
            $arrStockSkuInfo = [
                'sku_id' => $arrDetail['sku_id'],
                'stockout_amount' => $arrDetail['adjust_amount'],
            ];

            $arrStockOut['stockout_details'][] = $arrStockSkuInfo;
        }

        return $arrStockOut;
    }

    /**
     * 库存调减
     * @param $arrStockOut
     * @return array
     */
    protected function createBatchStockOut($arrStockOut)
    {
        $arrRet = [];
        Bd_Log::debug('调用库存模块参数 ' . print_r($arrStockOut,true));

        $arrRet =  $this->objStock->adjustStockout($arrStockOut['stockin_order_id'],
            $arrStockOut['warehouse_id'],
            $arrStockOut['inventory_type'],
            $arrStockOut['stockout_details']);

        Bd_Log::debug('调用库存模块返回值 ' . print_r($arrRet,true));
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
            return $arrDetail;
        }

        // 如果是生产日期型的，有效期天数必传
        if(Nscm_Define_Sku::SKU_EFFECT_FROM === $intSkuEffectType) {
            if(!is_numeric($intSkuEffectDay) || $intSkuEffectDay < 0) {
                Bd_Log::warning('sku有效期天数不正确 ' . $intSkuEffectDay);
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
            Bd_Log::warning('未识别的sku效期类型 ' . $intSkuEffectType);
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR);
        }

        return $arrDetail;
    }
}
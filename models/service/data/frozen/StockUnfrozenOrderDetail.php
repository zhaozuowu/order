<?php
/**
 * @name Service_Data_Frozen_StockUnfrozenOrderDetail
 * @desc 冻结单解冻明细
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_Frozen_StockUnfrozenOrderDetail
{
    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * @var Dao_Ral_Stock
     */
    protected $objDaoStock;

    protected $objDataOrderDetail;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoSku = new Dao_Ral_Sku();
        $this->objDataOrderDetail = new Service_Data_Frozen_StockFrozenOrderDetail();
        $this->objDaoStock = new Dao_Ral_Stock();
    }

    /**
     * 获取SKU详情
     * @param $arrSkuIds
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
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
     * 解冻
     * @param $arrInput
     * @return mixed
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function unfrozen($arrInput) {
        //查询冻结单
        $objFrozenOrder = Model_Orm_StockFrozenOrder::getStockFrozenOrderById($arrInput['stock_frozen_order_id']);
        if(empty($objFrozenOrder)) {
            Bd_Log::warning('unfrozen failed. frozen order not exits. ', print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_NOT_EXIST);
        }

        //查询冻结单明细
        $arrSkuIds = array_unique(array_column($arrInput['detail'], 'sku_id'));
        $arrFrozenDetail = $this->objDataOrderDetail->getOrderDetailBySku($arrInput['stock_frozen_order_id'], $arrSkuIds);
        if(empty($arrFrozenDetail)) {
            Bd_Log::warning('unfrozen failed. frozen order detail not exits. ', print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_DETAIL_NOT_EXIST);
        }

        //构造写库基础数据
        $arrSkuInfos = $this->getSkuInfos($arrSkuIds);
        $arrInsertUnfrozenDetail = $this->getInsertUnfrozenDetail($arrInput, $arrSkuInfos);
        $arrUpdateData = $this->buildUpdateData(
            $objFrozenOrder,
            $this->getUnfrozenInfoMap($arrInput, $arrSkuInfos),
            $this->getFrozenDetailMap($arrFrozenDetail)
        );

        //调用库存解冻
        $this->frozenSkuStock($arrInput, $arrSkuInfos);

        //写库
        $arrRes = $this->writeTransaction($arrUpdateData[0], $arrUpdateData[1], $arrInsertUnfrozenDetail);

        return $arrRes;
    }

    /**
     * 获取待插入解冻明细记录
     * @param $arrInput
     * @param $arrSkuInfos
     * @return array
     * @throws Order_BusinessError
     */
    protected function getInsertUnfrozenDetail($arrInput, $arrSkuInfos) {
        $arrOrderDetailArg = [];
        foreach ($arrInput['detail'] as $arrDetail) {
            if(intval($arrDetail['unfrozen_amount']) <= 0) {
                Bd_Log::warning('unfrozen amount invalid in detail param', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }

            $arrSkuInfo = $arrSkuInfos[$arrDetail['sku_id']];
            if(empty($arrSkuInfo)) {
                Bd_Log::warning("sku info is empty. sku id: " . $arrDetail['sku_id'], Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR);
            }

            // 根据商品效期类型，计算生产日期和有效期
            $arrDetail = Order_Util_Stock::getEffectTime($arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);
            $intCreator = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
            $strCreatorName = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];
            $arrDetail = [
                'stock_frozen_order_id'     => $arrInput['stock_frozen_order_id'],
                'warehouse_id'              => $arrInput['warehouse_id'],
                'sku_id'                    => $arrDetail['sku_id'],
                'upc_id'                    => $arrSkuInfo['min_upc']['upc_id'],
                'sku_name'                  => $arrSkuInfo['sku_name'],
                //'storage_location_id'       => $arrDetail['storage_location_id'],
                'unfrozen_amount'           => $arrDetail['unfrozen_amount'],
                'current_frozen_amount'     => $arrDetail['current_frozen_amount'] - $arrDetail['unfrozen_amount'],
                'is_defective'              => $arrDetail['is_defective'],
                'production_time'           => $arrDetail['production_time'],
                'expire_time'               => $arrDetail['expire_time'],
                'version'                   => 1,
                'unfrozen_user'             => $intCreator,
                'unfrozen_user_name'        => $strCreatorName,
            ];
            $arrOrderDetailArg[] = $arrDetail;
        }

        return $arrOrderDetailArg;
    }

    /**
     * 按照SKU聚合，查询冻结单详情个数
     * @param $arrInput
     * @return int
     */
    public function getOrderListCountGroupBySku($arrInput) {
        Bd_Log::trace(__METHOD__ . '  param ', 0, $arrInput);

        $arrSql = $this->buildGetOrderDetailListSql($arrInput);
        $ret = Model_Orm_StockFrozenOrderUnfrozenDetail::find( $arrSql['where'])->count('distinct sku_id');

        Bd_Log::trace(__METHOD__ . 'sql return: ' . $ret);
        return $ret;
    }

    /**
     * 按照SKU聚合，获取SKU ID
     * @param $arrInput
     * @return array
     */
    protected function getSkuIdsByOrderId($arrInput) {
        Bd_Log::trace(__METHOD__ . '  param ', 0, $arrInput);

        $arrSql = $this->buildGetOrderDetailListSql($arrInput);

        $arrRet = Model_Orm_StockFrozenOrderUnfrozenDetail::find(
            $arrSql['where'])->select(array('sku_id'))->groupBy(array('sku_id'))->orderBy(
                $arrSql['order_by'])->offset($arrSql['offset'])->limit($arrSql['limit'])->rows();
        Bd_Log::trace(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        if(!empty($arrRet)) {
            return array_values(array_column($arrRet, 'sku_id'));
        }
        return [];
    }

    /**
     * 按照SKU聚合，查询冻结单明细详情
     * @param $arrInput
     * @return array
     */
    public function getOrderListGroupBySku($arrInput) {
        $arrSkuIds = $this->getSkuIdsByOrderId($arrInput);
        if(empty($arrSkuIds)) {
            Bd_Log::warning('frozen order id: ' . $arrInput['stock_frozen_order_id'] . ' detail is empty.');
            return [];
        }
        $arrSql = $this->buildGetOrderListGroupBySkuSql($arrInput['stock_frozen_order_id'], $arrSkuIds);

        $arrRet = Model_Orm_StockFrozenOrderUnfrozenDetail::findRows($arrSql['columns'], $arrSql['where'],
            $arrSql['order_by']);
        Bd_Log::trace(__METHOD__ . 'sql return: ' . json_encode($arrRet));
        return $arrRet;
    }

    /**
     * @param $intOrderId
     * @param $arrSkuIds
     * @return array
     */
    protected function buildGetOrderListGroupBySkuSql($intOrderId, $arrSkuIds) {
        $arrSql = [];

        $arrWhere = [
            'is_delete'     => Order_Define_Const::NOT_DELETE,
        ];

        if(!empty($intOrderId)) {
            $arrWhere['stock_frozen_order_id'] = $intOrderId;
        }
        if(!empty($arrSkuIds)) {
            $arrWhere['sku_id'] = ['in', $arrSkuIds];
        }

        $arrSql['columns'] = Model_Orm_StockFrozenOrderUnfrozenDetail::getAllColumns();
        $arrSql['order_by'] = ['warehouse_id' => 'asc', 'id' => 'desc'];
        $arrSql['where'] = $arrWhere;
        return $arrSql;
    }

    /**
     * 拼装冻结单解冻明细查询条件
     * @param $arrInput
     * @return array
     */
    protected function buildGetOrderDetailListSql($arrInput) {
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

        $arrSql['columns'] = Model_Orm_StockFrozenOrderUnfrozenDetail::getAllColumns();
        $arrSql['order_by'] = ['warehouse_id' => 'asc', 'id' => 'desc'];
        $arrSql['limit'] = $intLimit;
        $arrSql['offset'] = $intOffset;
        $arrSql['where'] = $arrWhere;
        return $arrSql;
    }

    /**
     * build update data
     * @param $objFrozenOrder
     * @param $arrUnfrozenInfoMap
     * @param $arrFrozenDetailMap
     * @return array
     * @throws Order_BusinessError
     */
    protected function buildUpdateData($objFrozenOrder, $arrUnfrozenInfoMap, $arrFrozenDetailMap)
    {
        $arrUpdateFrozenOrderColumns = [];

        //如果冻结单状态为冻结，则置为部分冻结（解冻一定会扣减库存）
        if (Order_Define_StockFrozenOrder::FROZEN_ORDER_STATUS_FROZEN == $objFrozenOrder->order_status) {
            $arrUpdateFrozenOrderColumns['order_status'] = Order_Define_StockFrozenOrder::FROZEN_ORDER_STATUS_PART_FROZEN;
        }

        $arrUpdateFrozenDetail = [];
        foreach ($arrUnfrozenInfoMap['detail'] as $intUniqKey => $arrUnfrozenInfoItem) {
            $arrFrozenDetail = $arrFrozenDetailMap[$intUniqKey];

            //数据校验
            if (empty($arrFrozenDetail)) {
                Bd_Log::warning(__METHOD__ . 'frozen detail not find');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_DETAIL_NOT_FOUND);
            }
            if ($arrUnfrozenInfoItem['current_frozen_amount'] != $arrFrozenDetail['current_frozen_amount']) {
                Bd_Log::warning(__METHOD__ . 'current frozen amount not match');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_UNFROZEN_CURRENT_FROZEN_AMOUNT_NOT_NATCH);
            }
            if ($arrUnfrozenInfoItem['unfrozen_amount'] > $arrFrozenDetail['current_frozen_amount']) {
                Bd_Log::warning(__METHOD__ . 'unfrozen amount over frozen amount');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_UNFROZEN_AMOUNT_OVER_FROZEN_AMOUNT);
            }

            //扣减冻结单当前冻结量
            $objFrozenOrder->current_total_frozen_amount -=  $arrUnfrozenInfoItem['unfrozen_amount'];

            //如果冻结单当前总冻结量为0，则冻结单关闭
            if (0 == $objFrozenOrder->current_total_frozen_amount) {
                $arrUpdateFrozenOrderColumns['order_status'] = Order_Define_StockFrozenOrder::FROZEN_ORDER_STATUS_CLOSED;
                $arrUpdateFrozenOrderColumns['close_time'] = time();
                $arrUpdateFrozenOrderColumns['close_user_id'] = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
                $arrUpdateFrozenOrderColumns['close_user_name'] = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];
            }

            //记录冻结单详情更新字段及条件
            $arrUpdateFrozenDetail[] = [
                'columns' => [
                    'current_frozen_amount' => $arrFrozenDetail['current_frozen_amount'] - $arrUnfrozenInfoItem['unfrozen_amount'],
                    'version' => $arrFrozenDetail['version'] + 1
                ],
                'conditions' => [
                    'id' => $arrFrozenDetail['id'],
                    'version' => $arrFrozenDetail['version']
                ]
            ];
         }

        //记录冻结单更新字段及条件
        $arrUpdateFrozenOrderColumns['current_total_frozen_amount'] = $objFrozenOrder->current_total_frozen_amount;
        $arrUpdateFrozenOrderColumns['version'] = $objFrozenOrder->version + 1;
        $arrUpdateFrozenOrder = [
            'columns' => $arrUpdateFrozenOrderColumns,
            'conditions' => [
                'id' =>$objFrozenOrder->id,
                'version' => $objFrozenOrder->version
            ]
        ];

        return [$arrUpdateFrozenOrder, $arrUpdateFrozenDetail];
    }

    /**
     * 获取解冻数据Map
     * @param $arrInput
     * @param $arrSkuInfos
     * @return array
     * @throws Order_BusinessError
     */
    protected function getUnfrozenInfoMap($arrInput, $arrSkuInfos) {
        $arrRes = [];
        $arrRes['warehouse_id'] = $arrInput['warehouse_id'];
        $arrRes['stock_frozen_order_id'] = $arrInput['stock_frozen_order_id'];
        $arrResItem = [];
        foreach ($arrInput['detail'] as $arrUnfrozenItem) {
            $intExpireTime = Order_Util_Stock::getExpireTime(
                $arrUnfrozenItem['production_or_expire_time'],
                $arrSkuInfos[$arrUnfrozenItem['sku_id']]['sku_effect_type'],
                $arrSkuInfos[$arrUnfrozenItem['sku_id']]['sku_effect_day']
            );
            $strUniqKey = $this->getUnfrozenInfoUniqKey(
                $arrUnfrozenItem['sku_id'],
                $intExpireTime,
                $arrUnfrozenItem['is_defective']
            );
            if (array_key_exists($strUniqKey, $arrResItem)) {
                Bd_Log::warning(__METHOD__ . 'sku_id-expire_time-is_defective repeated');
                Order_BusinessError::throwException(
                    Order_Error_Code::NWMS_UNFROZEN_PARAM_REPEATED,
                    sprintf(
                        '解冻参数重复，商品ID：%s，产效期：%s，质量状态：%s',
                        $arrUnfrozenItem['sku_id'],
                        $arrUnfrozenItem['production_or_expire_time'],
                        $arrUnfrozenItem['is_defective']
                    )
                );
            }
            $arrResItem[$strUniqKey] = $arrUnfrozenItem;
        }
        $arrRes['detail'] = $arrResItem;
        return $arrRes;
    }

    /**
     * 获取冻结详情数据Map
     * @param $arrFrozenDetail
     * @return array
     */
    protected function getFrozenDetailMap($arrFrozenDetail) {
        $arrRes = [];
        foreach ($arrFrozenDetail as $arrFrozenDetailItem) {
            $intUniqKey = $this->getUnfrozenInfoUniqKey(
                $arrFrozenDetailItem['sku_id'],
                $arrFrozenDetailItem['expire_time'],
                $arrFrozenDetailItem['is_defective']
            );
            $arrRes[$intUniqKey] = $arrFrozenDetailItem;
        }
        return $arrRes;
    }

    /**
     * 获取sku_id-到效期-质量状态的唯一key
     * @param $intSkuId
     * @param $intExpireTime
     * @param $intIsDefective
     * @return string
     */
    protected function getUnfrozenInfoUniqKey($intSkuId, $intExpireTime, $intIsDefective) {
       return $intSkuId . '-' . $intExpireTime . '-' . $intIsDefective;
    }

    /**
     * 写库
     * @param $arrUpdateFrozenOrder
     * @param $arrUpdateFrozenDetail
     * @param $arrInsertUnfrozenDetail
     * @return array
     * @throws Exception
     */
    protected function writeTransaction($arrUpdateFrozenOrder, $arrUpdateFrozenDetail, $arrInsertUnfrozenDetail) {
        $objDbConn = Wm_Orm_Connection::getConnection('nwms_order_cluster');
        $arrRes = $objDbConn->transaction(function() use (
            $arrUpdateFrozenOrder,
            $arrUpdateFrozenDetail,
            $arrInsertUnfrozenDetail
        ) {
            //更新冻结单
            $intAffectRow = Model_Orm_StockFrozenOrder::find()->update(
                $arrUpdateFrozenOrder['columns'],
                $arrUpdateFrozenOrder['conditions']
            );
            if (1 != $intAffectRow) {
                Bd_Log::warning(__METHOD__ . 'unfrozen check version fail');
                Order_BusinessError::throwException(Order_Error_Code::NWMS_UNFROZEN_CHECK_VERSION_FAIL);
            }

            //更新冻结单明细
            foreach ($arrUpdateFrozenDetail as $arrItem) {
                $intAffectRow = Model_Orm_StockFrozenOrderDetail::find()->update(
                    $arrItem['columns'],
                    $arrItem['conditions']
                );
                if (1 != $intAffectRow) {
                    Bd_Log::warning(__METHOD__ . 'unfrozen check version fail');
                    Order_BusinessError::throwException(Order_Error_Code::NWMS_UNFROZEN_CHECK_VERSION_FAIL);
                }
            }

            //新建冻结单明细
            Model_Orm_StockFrozenOrderUnfrozenDetail::batchInsert($arrInsertUnfrozenDetail);
        });

        return $arrRes;
    }

    /**
     * 调用库存解冻
     * @param $arrInput
     * @param $arrSkuInfos
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function frozenSkuStock($arrInput, $arrSkuInfos)
    {
        $arrPram = [
            'warehouse_id' => $arrInput['warehouse_id'],
            'ext_order_id' => $arrInput['stock_frozen_order_id'],
            'frozen_type'  => $arrInput['frozen_type'],
        ];
        foreach ($arrInput['detail'] as $arrItem) {
            $arrSkuInfo = $arrSkuInfos[$arrItem['sku_id']];
            $intExpireTime = Order_Util_Stock::getExpireTime(
                $arrItem['production_or_expire_time'],
                $arrSkuInfo['sku_effect_type'],
                $arrSkuInfo['sku_effect_day']
            );
            $arrDetail = [
                'sku_id' => $arrItem['sku_id'],
                'frozen_amount' => $arrItem['current_frozen_amount'],
                'unfreeze_amount' => $arrItem['unfrozen_amount'],
                'is_defective' => $arrItem['is_defective'],
                'expiration_time' => $intExpireTime
            ];
            $arrPram['details'][] = $arrDetail;
        }

        $this->objDaoStock->unfrozenStock($arrPram);
    }
}
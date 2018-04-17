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


    protected $objDataOrderDetail;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoSku = new Dao_Ral_Sku();
        $this->objDataOrderDetail = new Service_Data_Frozen_StockFrozenOrderDetail();
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
     * @throws Order_BusinessError
     */
    public function unfrozen($arrInput) {
        $arrSkuIds = array_unique(array_column($arrInput['detail'], 'sku_id'));
        $arrFrozenDetail = $this->objDataOrderDetail->getOrderDetailBySku($arrInput['stock_frozen_order_id'], $arrSkuIds);
        if(empty($arrFrozenDetail)) {
            Bd_Log::warning('unfrozen failed. frozen order detail not exits. ', print_r($arrInput, true));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_FROZEN_ORDER_DETAIL_NOT_EXIST);
        }

        $this->checkUnfrozenParam($arrInput, $arrFrozenDetail);
        $arrSkuInfos = $this->getSkuInfos($arrSkuIds);
        $arrInsertArg = $this->getInsertUnfrozenArg($arrInput, $arrSkuInfos);

        $intInsertRet = Model_Orm_StockFrozenOrderUnfrozenDetail::batchInsert($arrInsertArg);

        return $intInsertRet;
    }


    protected function checkUnfrozenParam($arrInput, $arrFrozenDetail) {
        //todo
    }

    protected function getInsertUnfrozenArg($arrInput, $arrSkuInfos) {
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
            $arrDetail = $this->getEffectTime($arrDetail, $arrSkuInfo['sku_effect_type'], $arrSkuInfo['sku_effect_day']);

            $intCreator = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
            $strCreatorName = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_name'];

            $arrDetail = [
                'stock_frozen_order_id'     => $arrInput['stock_frozen_order_id'],
                'warehouse_id'              => $arrInput['warehouse_id'],
                'sku_id'                    => $arrDetail['sku_id'],
                'upc_id'                    => $arrSkuInfo['min_upc']['upc_id'],
                'sku_name'                  => $arrSkuInfo['sku_name'],
                'storage_location_id'       => $arrDetail['storage_location_id'],
                'unfrozen_amount'           => $arrDetail['unfrozen_amount'],
                'current_frozen_amount'     => $arrDetail['current_frozen_amount'],
                'is_defective'              => $arrInput['is_defective'],
                'production_time'           => $arrDetail['production_time'],
                'expire_time'               => $arrDetail['expire_time'],
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


}
<?php
/**
 * @name Service_Data_Frozen_StockFrozenOrderDetail
 * @desc 冻结单明细
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_Frozen_StockFrozenOrderDetail
{
    /**
     * @var Dao_Ral_Sku
     */
    protected $objDaoSku;

    /**
     * init
     */
    public function __construct() {
        $this->objDaoSku = new Dao_Ral_Sku();
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
     * 按照SKU聚合，查询冻结单详情个数
     * @param $arrInput
     * @return int
     */
    public function getOrderListCountGroupBySku($arrInput) {
        Bd_Log::trace(__METHOD__ . '  param ', 0, $arrInput);

        $arrSql = $this->buildGetOrderDetailListSql($arrInput);
        $ret = Model_Orm_StockFrozenOrderDetail::find( $arrSql['where'])->count('distinct sku_id');

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

        $arrRet = Model_Orm_StockFrozenOrderDetail::find(
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

        $arrRet = $this->getOrderDetailBySku($arrInput['stock_frozen_order_id'], $arrSkuIds);

        $arrSkuInfos = $this->getSkuInfos($arrSkuIds);

        return Order_Util::mergeSkuInfo($arrRet, $arrSkuInfos);

    }

    /**
     * 查询冻结单明细
     * @param $intFrozenOrderId
     * @param $arrSkuIds
     * @return array
     */
    public function getOrderDetailBySku($intFrozenOrderId, $arrSkuIds) {
        $arrSql = $this->buildGetOrderListGroupBySkuSql($intFrozenOrderId, $arrSkuIds);

        $arrRet = Model_Orm_StockFrozenOrderDetail::findRows($arrSql['columns'], $arrSql['where'],
            $arrSql['order_by']);
        //Bd_Log::trace(__METHOD__ . 'sql return: ' . json_encode($arrRet));
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

        $arrSql['columns'] = Model_Orm_StockFrozenOrderDetail::getAllColumns();
        $arrSql['order_by'] = ['warehouse_id' => 'asc', 'id' => 'desc'];
        $arrSql['where'] = $arrWhere;
        return $arrSql;
    }

    /**
     * 拼装冻结单查询条件
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

        $arrSql['columns'] = Model_Orm_StockFrozenOrderDetail::getAllColumns();
        $arrSql['order_by'] = ['warehouse_id' => 'asc', 'id' => 'desc'];
        $arrSql['limit'] = $intLimit;
        $arrSql['offset'] = $intOffset;
        $arrSql['where'] = $arrWhere;
        return $arrSql;
    }


}
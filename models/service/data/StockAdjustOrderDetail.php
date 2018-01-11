<?php
/**
 * @name Service_Data_StockAdjustOrderDetail
 * @desc 库存调整单详情
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Data_StockAdjustOrderDetail
{
    /**
     * 查询采购单详情
     * @param $arrInput
     * @return array
     */
    public function get($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        // 获取所有字段
        $arrColumns = Model_Orm_StockAdjustOrderDetail::getAllColumns();

        $arrConditions = $this->getConditions($arrInput);
        $arrOrderBy = ['sku_id' => 'asc'];
        if(empty($arrInput['page_num'])) {
            $arrInput['page_num'] = 1;
        }
        if(empty($arrInput['page_size'])) {
            $arrInput['page_size'] = 20;
        }
        $intOffset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
        $intLimit = $arrInput['page_size'];

        $ret = Model_Orm_StockAdjustOrderDetail::findRows($arrColumns, $arrConditions, $arrOrderBy, $intOffset, $intLimit);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . json_encode($ret));
        return $ret;
    }

    /**
     * 查询符合条件的采购单详情个数
     * @param $arrInput
     * @return int
     */
    public function getCount($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrConditions = $this->getConditions($arrInput);
        $ret = Model_Orm_StockAdjustOrderDetail::count( $arrConditions);
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
        if(!empty($arrInput['sku_id'])) {
            $arrFormatInput['sku_id'] = $arrInput['sku_id'];
        }
        if(!empty($arrInput['adjust_type'])) {
            $intAdjustType = Order_Define_AdjustOrder::ADJUST_TYPE_MAP[$arrInput['adjust_type']];
            if(empty($intAdjustType)) {
                Bd_Log::warning('调整单类型不正确', Order_Error_Code::PARAMS_ERROR, $arrInput);
                Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
            }
            $arrFormatInput['adjust_type'] = $arrInput['adjust_type'];
        }

        if (!empty($arrInput['begin_date'])) {
            $arrFormatInput['create_time'][] = ['>=', intval($arrInput['begin_date'])];
        }
        if (!empty($arrInput['end_date'])) {
            $arrFormatInput['create_time'][] = ['<=', intval($arrInput['end_date'])];
        }

        return $arrFormatInput;
    }
}
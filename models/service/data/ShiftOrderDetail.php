<?php
/**
 * @name Service_Data_ShiftOrderDetail
 * @desc 移位单详情
 * @author songwenkai@iwaimai.baidu.com
 */

class Service_Data_ShiftOrderDetail
{
    /**
     * 查询移位详情
     * @param $arrInput
     * @return array
     */
    public function get($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        // 获取所有字段
        $arrColumns = Model_Orm_ShiftOrderDetail::getAllColumns();

        $arrConditions = $this->getConditions($arrInput);
        // 库存调整明细：仓库ID排期（ID从小到大）> 创建时间倒序
        $arrOrderBy = ['warehouse_id' => 'asc', 'id' => 'desc'];
        if(empty($arrInput['page_num'])) {
            $arrInput['page_num'] = 1;
        }
        if(empty($arrInput['page_size'])) {
            $arrInput['page_size'] = 20;
        }
        $intOffset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
        $intLimit = $arrInput['page_size'];

        $ret = Model_Orm_StockAdjustOrderDetail::findRows($arrColumns, $arrConditions, $arrOrderBy, $intOffset, $intLimit);
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
        $ret = Model_Orm_ShiftOrderDetail::count( $arrConditions);
        Bd_Log::debug(__METHOD__ . 'sql return: ' . $ret);
        return $ret;
    }

    /**
     * 按照sku_id 分组查询count
     * @param $arrInput
     * @return int
     */
    public function getCountWithGroup($arrInput) {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrConditions = $this->getConditions($arrInput);
        $ret = Model_Orm_ShiftOrderDetail::find($arrConditions)->count('distinct sku_id');
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
//        if(!empty($arrInput['warehouse_ids'])) {
//            $arrFormatInput['warehouse_id'] = ['in', $arrInput['warehouse_ids']];
//        }
//        if(!empty($arrInput['warehouse_id'])) {
//            $arrFormatInput['warehouse_id'] = $arrInput['warehouse_id'];
//        }
        if(!empty($arrInput['shift_order_id'])) {
            $arrFormatInput['shift_order_id'] = $arrInput['shift_order_id'];
        }
        return $arrFormatInput;
    }
}
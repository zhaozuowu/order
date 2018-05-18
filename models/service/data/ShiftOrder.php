<?php
/**
 * @name    Service_Data_ShiftOrder
 * @desc 移位单
 * @author songwenkai@iwaimai.baidu.com
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
     * 新建移位单
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
     * 取消移位单
     * @param $arrInput
     * @return arrayShift
     */
    public function cancelShiftOrder($arrInput)
    {
        $condition = ['shift_order_id' => $arrInput['shift_order_id']];
        $ormOrderInfo = Model_Orm_ShiftOrder::findOne($condition);
        $ormOrderInfo->status = 0;
        $intAffectRows = $ormOrderInfo->update();
        if (1 !== $intAffectRows) {
            Bd_Log::warning(sprintf("cancel shift order failed order_id[%s] ",$arrInput['shift_order_id']));
            return false;
        }
        Bd_Log::trace('cancel shift order return ' . print_r($intAffectRows, true));
        return true;
    }

    /**
     * 查询移位单
     * 如果传入page_size，则分页返回结果，否则不分页。
     * @param $arrInput
     * @return array
     */
    public function get($arrInput)
    {
        Bd_Log::debug(__METHOD__ . '  param ', 0, $arrInput);

        $arrColumns    = Model_Orm_ShiftOrder::getAllColumns();
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

        $arrRet = Model_Orm_ShiftOrder::findRows($arrColumns, $arrConditions, $arrOrderBy, $intOffset, $intLimit);
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
        $ret           = Model_Orm_ShiftOrder::count($arrConditions);
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
        if (!empty($arrInput['shift_order_id'])) {
            $arrFormatInput['shift_order_id'] = $arrInput['shift_order_id'];
        }
        if (!empty($arrInput['status'])) {
            $arrFormatInput['status'] = $arrInput['status'];
        }
        if (!empty($arrInput['source_location'])) {
            $arrFormatInput['source_location'] = $arrInput['source_location'];
        }
        if (!empty($arrInput['target_location'])) {
            $arrFormatInput['target_location'] = $arrInput['target_location'];
        }
        if (!empty($arrInput['sku_id'])) {
            $arrFormatInput['sku_id'][] = ['like ',$arrInput['sku_id']];
        }
        if (!empty($arrInput['sku_name'])) {
            $arrFormatInput['sku_name'][] = ['like ',$arrInput['sku_name']];
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
     * 获取移位单
     * @param $stock_shift_order_id
     * @return Model_Orm_ShiftOrder
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
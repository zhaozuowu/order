<?php
/**
 * @name Action_GetOrder
 * @desc 查询冻结单列表
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids'             => 'arr|required|arr_min[1]|type[int]',
        'stock_frozen_order_id'     => 'regex|patern[/^(F\d{13})?$/]',
        'sku_id'                    => 'int|default[0]',
        'create_type'               => 'int|default[0]',
        'order_status'              => 'int|default[0]',
        'create_time_start'         => 'int|default[0]',
        'create_time_end'           => 'int|default[0]',
        'close_time_start'          => 'int|default[0]',
        'close_time_end'            => 'int|default[0]',
        'page_num'                  => 'int|default[1]',
        'page_size'                 => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_Frozen_GetOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertString2Array('warehouse_ids');
        $this->objPage = new Service_Page_Frozen_GetOrder();
    }

    /**
     * 将逗号分隔字符串转换为数组
     * @param string $strKey
     */
    protected function convertString2Array($strKey) {
        if ($this->intMethod == Order_Define_Const::METHOD_GET) {
            if(!empty($this->arrReqGet[$strKey])) {
                $this->arrReqGet[$strKey] = explode(',', $this->arrReqGet[$strKey]);
            }
        } else if ($this->intMethod == Order_Define_Const::METHOD_POST) {
            if(!empty($this->arrReqPost[$strKey])) {
                $this->arrReqPost[$strKey] = explode(',', $this->arrReqPost[$strKey]);
            }
        }
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult = [
            'list' => [],
            'total' => 0,
        ];

        if(empty($data['list'])) {
            return $arrFormatResult;
        }

        $arrFormatResult['total'] = $data['total'];

        $arrOrderList = $data['list'];
        foreach ($arrOrderList as $arrOrder) {
            $arrFormatOrder = [];
            $arrFormatOrder['stock_frozen_order_id'] =
                empty($arrOrder['stock_frozen_order_id']) ? '' : Nscm_Define_OrderPrefix::F . strval($arrOrder['stock_frozen_order_id']);
            $arrFormatOrder['warehouse_id'] = empty($arrOrder['warehouse_id']) ? '' : strval($arrOrder['warehouse_id']);
            $arrFormatOrder['warehouse_name'] = empty($arrOrder['warehouse_name']) ? '' : strval($arrOrder['warehouse_name']);
            $arrFormatOrder['sku_amount'] = empty($arrOrder['sku_amount']) ? '' : strval($arrOrder['sku_amount']);
            $arrFormatOrder['origin_total_frozen_amount'] =
                empty($arrOrder['origin_total_frozen_amount']) ? '' : strval($arrOrder['origin_total_frozen_amount']);
            $arrFormatOrder['remark'] = empty($arrOrder['remark']) ? '' : strval($arrOrder['remark']);
            $arrFormatOrder['order_status'] = empty($arrOrder['order_status']) ? '' : strval($arrOrder['order_status']);
            $arrFormatOrder['order_status_text'] =
                empty($arrOrder['order_status']) ? '' : Order_Define_StockFrozenOrder::FROZEN_ORDER_STATUS_MAP[$arrOrder['order_status']];
            $arrFormatOrder['create_type_text'] =
                empty($arrOrder['create_type']) ? '' : Order_Define_StockFrozenOrder::FROZEN_ORDER_CREATE_MAP[$arrOrder['create_type']];
            $arrFormatOrder['creator_name'] = empty($arrOrder['creator_name']) ? '' : strval($arrOrder['creator_name']);
            $arrFormatOrder['create_time'] = empty($arrOrder['create_time']) ? '' : strval($arrOrder['create_time']);
            $arrFormatOrder['close_user_name'] = empty($arrOrder['close_user_name']) ? '' : strval($arrOrder['close_user_name']);
            $arrFormatOrder['close_time'] = empty($arrOrder['close_time']) ? '' : strval($arrOrder['close_time']);

            $arrFormatResult['list'][] = $arrFormatOrder;
        }

        return $arrFormatResult;
    }
}
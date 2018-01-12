<?php
/**
 * @name Action_GetOrder
 * @desc 查询库存调整单
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
        'stock_adjust_order_id'     => 'regex|patern[/^(SAO\d{13})?$/]',
        'adjust_type'               => 'int|optional',
        'begin_date'                => 'int|optional',
        'end_date'                  => 'int|optional',
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
     * @var Service_Page_Adjust_GetOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertWarehouseIds2Array();
        $this->objPage = new Service_Page_Adjust_GetOrder();
    }

    protected function convertWarehouseIds2Array() {
        if ($this->intMethod == Order_Define_Const::METHOD_GET) {
            if(!empty($this->arrReqGet['warehouse_ids'])) {
                $this->arrReqGet['warehouse_ids'] = explode(',', $this->arrReqGet['warehouse_ids']);
            }
        } else if ($this->intMethod == Order_Define_Const::METHOD_POST) {
            if(!empty($this->arrReqPost['warehouse_ids'])) {
                $this->arrReqPost['warehouse_ids'] = explode(',', $this->arrReqPost['warehouse_ids']);
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
            'stock_adjust_order_list' => [],
            'total' => 0,
        ];

        if(empty($data['stock_adjust_order_list'])) {
            return $arrFormatResult;
        }

        $arrFormatResult['total'] = $data['total'];

        $arrOrderList = $data['stock_adjust_order_list'];
        foreach ($arrOrderList as $arrOrder) {
            $arrFormatOrder = [];
            $arrFormatOrder['stock_adjust_order_id'] = empty($arrOrder['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($arrOrder['stock_adjust_order_id']);
            $arrFormatOrder['warehouse_id'] = empty($arrOrder['warehouse_id']) ? '' : intval($arrOrder['warehouse_id']);
            $arrFormatOrder['warehouse_name'] = empty($arrOrder['warehouse_name']) ? '' : strval($arrOrder['warehouse_name']);
            $arrFormatOrder['adjust_type'] = empty($arrOrder['adjust_type']) ? '' :
                Nscm_Define_Stock::ADJUST_TYPE_MAP[intval($arrOrder['adjust_type'])];
            $arrFormatOrder['total_adjust_amount'] = empty($arrOrder['total_adjust_amount']) ? '' : intval($arrOrder['total_adjust_amount']);
            $arrFormatOrder['remark'] = empty($arrOrder['remark']) ? '' : strval($arrOrder['remark']);
            $arrFormatOrder['create_time'] = empty($arrOrder['create_time']) ? '' : intval($arrOrder['create_time']);
            $arrFormatOrder['creator_name'] = empty($arrOrder['creator_name']) ? '' : strval($arrOrder['creator_name']);

            $arrFormatResult['stock_adjust_order_list'][] = $arrFormatOrder;
        }

        return $arrFormatResult;
    }
}
<?php
/**
 * @name Action_GetOrderDetailForm
 * @desc 导出库存调整单SKU
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrderDetailForm extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids'             => 'arr|required|arr_min[1]|type[int]',
        'stock_adjust_order_id'     => 'str|optional',
        'adjust_type'               => 'int|optional',
        'begin_date'                => 'int|required',
        'end_date'                  => 'int|required',
        'page_num'                  => 'int|default[1]',
        'page_size'                 => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_Adjust_ExportOrderDetail
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertWarehouseIds2Array();
        $this->objPage = new Service_Page_Adjust_ExportOrderDetail();
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
            'total' => 0,
            'list' => [],
        ];

        if(empty($data['list'])) {
            return $arrFormatResult;
        }

        foreach ($data['list'] as $detail) {
            $arrFormatDetail['city_id']    = empty($detail['city_id']) ? '' : intval($detail['city_id']);
            $arrFormatDetail['city_name']    = empty($detail['city_name']) ? '' : strval($detail['city_name']);
            $arrFormatDetail['warehouse_name']    = empty($detail['warehouse_name']) ? '' : strval($detail['warehouse_name']);
            $arrFormatDetail['warehouse_id']    = empty($detail['warehouse_id']) ? '' : intval($detail['warehouse_id']);
            $arrFormatDetail['stock_adjust_order_id']    = empty($detail['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($detail['stock_adjust_order_id']);
            $arrFormatDetail['adjust_type']    = empty($detail['adjust_type']) ? '' : Nscm_Define_Stock::ADJUST_TYPE_MAP[intval($detail['adjust_type'])];
            $arrFormatDetail['sku_id']    = empty($detail['sku_id']) ? '' : intval($detail['sku_id']);
            $arrFormatDetail['upc_id']    = empty($detail['upc_id']) ? '' : strval($detail['upc_id']);
            $arrFormatDetail['sku_name']    = empty($detail['sku_name']) ? '' : strval($detail['sku_name']);
            $arrFormatDetail['sku_category_1_name']    = empty($detail['sku_category_1_name']) ? '' : strval($detail['sku_category_1_name']);
            $arrFormatDetail['sku_category_2_name']    = empty($detail['sku_category_2_name']) ? '' : strval($detail['sku_category_2_name']);
            $arrFormatDetail['sku_category_3_name']    = empty($detail['sku_category_3_name']) ? '' : strval($detail['sku_category_3_name']);
            $arrFormatDetail['sku_from_country']    = empty($detail['sku_from_country']) ? '' : strval($detail['sku_from_country']);
            $arrFormatDetail['sku_net']    = empty($detail['sku_net']) ? '' : intval($detail['sku_net']);
            $arrFormatDetail['adjust_amount']    = empty($detail['adjust_amount']) ? '' : intval($detail['adjust_amount']);
            $arrFormatDetail['unit_price']    = empty($detail['unit_price']) ? '' : strval($detail['unit_price'] / 100) ;
            $arrFormatDetail['unit_price_tax']    = empty($detail['unit_price_tax']) ? '' : strval($detail['unit_price_tax'] / 100);

            if(empty($detail['adjust_amount']) || empty($detail['unit_price']) || empty($detail['unit_price_tax'])) {
                $arrFormatResult['total_unit_price']    = '';
                $arrFormatResult['total_unit_price_tax']    = '';
            } else {
                $total_unit_price = intval($detail['unit_price']) * intval($detail['adjust_amount']);
                $total_unit_price_tax = intval($detail['unit_price_tax']) * intval($detail['adjust_amount']);

                $arrFormatDetail['total_unit_price']    = strval($total_unit_price / 100);
                $arrFormatDetail['total_unit_price_tax']    = strval($total_unit_price_tax / 100);
            }

            $arrFormatDetail['create_time_str']    = empty($detail['create_time']) ? '' : strval(date('Y-m-d H:i:s',$detail['create_time']));
            $arrFormatDetail['creator_name']    = empty($detail['creator_name']) ? '' : strval($detail['creator_name']);

            $arrFormatResult['list'][] = $arrFormatDetail;
        }

        $arrFormatResult['total'] = $data['total'];

        return $arrFormatResult;
    }
}
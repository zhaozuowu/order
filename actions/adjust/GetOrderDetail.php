<?php
/**
 * @name Action_GetOrderDetail
 * @desc 获取采购单详情
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'              => 'int|required',
        'stock_adjust_order_id'     => 'regex|patern[/^(SAO\d{13})?$/]',
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
     * @var Service_Page_Adjust_GetOrderDetail
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Adjust_GetOrderDetail();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult = [
            'stock_adjust_order_id'     => '',
            'adjust_type'               => '',
            'total_adjust_amount'       => '',
            'warehouse_name'            => '',
            'creator_name'              => '',
            'remark'                    => '',
            'stock_adjust_order_detail' => [
                'total' => 0,
                'detail' => [],
            ],
        ];

        $arrFormatResult['stock_adjust_order_id']    = empty($data['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($data['stock_adjust_order_id']);
        $arrFormatResult['adjust_type']              = empty($data['adjust_type']) ? '' : Nscm_Define_Stock::ADJUST_TYPE_MAP[intval($data['adjust_type'])];
        $arrFormatResult['total_adjust_amount']      = empty($data['total_adjust_amount']) ? '' : intval($data['total_adjust_amount']);
        $arrFormatResult['warehouse_name']           = empty($data['warehouse_name']) ? '' : strval($data['warehouse_name']);
        $arrFormatResult['creator_name']             = empty($data['creator_name']) ? '' : strval($data['creator_name']);
        $arrFormatResult['remark']                   = empty($data['remark']) ? '' : strval($data['remark']);

        if(empty($data['stock_adjust_order_detail'])) {
            return $arrFormatResult;
        }

        $arrFormatResult['stock_adjust_order_detail']['total'] = $data['stock_adjust_order_detail']['total'];

        $arrOrderSkuList = $data['stock_adjust_order_detail']['detail'];
        foreach ($arrOrderSkuList as $arrOrder) {
            $arrFormatOrder                     = [];
            $arrFormatOrder['sku_id']           = empty($arrOrder['sku_id']) ? '' : intval($arrOrder['sku_id']);
            $arrFormatOrder['sku_name']         = empty($arrOrder['sku_name']) ? '' : strval($arrOrder['sku_name']);
            $arrFormatOrder['upc_id']           = empty($arrOrder['upc_id']) ? '' : strval($arrOrder['upc_id']);
            $arrFormatOrder['upc_unit']         = empty($arrOrder['upc_unit']) ? '' : strval($arrOrder['upc_unit']);
            $arrFormatOrder['sku_net']          = empty($arrOrder['sku_net']) ? '' : strval($arrOrder['sku_net']);
            $arrFormatOrder['sku_net_unit']     = empty($arrOrder['sku_net_unit']) ? '' : strval($arrOrder['sku_net_unit']);
            $arrFormatOrder['unit_price_tax']   = empty($arrOrder['unit_price_tax']) ? '' : intval($arrOrder['unit_price_tax']);
            $arrFormatOrder['unit_price']       = empty($arrOrder['unit_price']) ? '' : intval($arrOrder['unit_price']);
            $arrFormatOrder['adjust_amount']    = empty($arrOrder['adjust_amount']) ? '' : intval($arrOrder['adjust_amount']);
            $arrFormatOrder['production_time']  = empty($arrOrder['production_time']) ? '' : intval($arrOrder['production_time']);
            $arrFormatOrder['expire_time']      = empty($arrOrder['expire_time']) ? '' : intval($arrOrder['expire_time']);

            $arrFormatResult['stock_adjust_order_detail']['detail'][] = $arrFormatOrder;
        }

        return $arrFormatResult;
    }
}
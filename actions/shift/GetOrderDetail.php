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
        'warehouse_id'          => 'int|required',
        'stock_adjust_order_id' => 'regex|patern[/^(SAO\d{13})?$/]',
        'page_num'              => 'int|default[1]',
        'page_size'             => 'int|required',
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
            'adjust_amount_type'        => '',
            'total_adjust_amount'       => '',
            'warehouse_name'            => '',
            'creator_name'              => '',
            'remark'                    => '',
            'create_time'               => '',
            'stock_adjust_order_detail' => [
                'total'  => 0,
                'detail' => [],
            ],
        ];

        $arrFormatResult['stock_adjust_order_id'] = empty($data['stock_adjust_order_id']) ? '' : Nscm_Define_OrderPrefix::SAO . intval($data['stock_adjust_order_id']);
        $arrFormatResult['adjust_type']           = empty($data['adjust_type']) ? '' : Nscm_Define_Stock::ADJUST_TYPE_MAP[intval($data['adjust_type'])];
        $arrFormatResult['adjust_amount_type']    = empty($data['adjust_amount_type']) ? '' : strval($data['adjust_amount_type']);
        $arrFormatResult['total_adjust_amount']   = empty($data['total_adjust_amount']) ? '' : intval($data['total_adjust_amount']);
        $arrFormatResult['warehouse_name']        = empty($data['warehouse_name']) ? '' : strval($data['warehouse_name']);
        $arrFormatResult['creator_name']          = empty($data['creator_name']) ? '' : strval($data['creator_name']);
        $arrFormatResult['remark']                = empty($data['remark']) ? '' : strval($data['remark']);
        $arrFormatResult['create_time']           = empty($data['create_time']) ? '' : strval($data['create_time']);

        if (empty($data['stock_adjust_order_detail'])) {
            return $arrFormatResult;
        }

        $arrFormatResult['stock_adjust_order_detail']['total'] = $data['stock_adjust_order_detail']['total'];


        $mapSku2Info = [];

        $arrOrderSkuList = $data['stock_adjust_order_detail']['detail'];
        foreach ($arrOrderSkuList as $arrOrder) {
            $arrFormatOrder                      = [];
            $arrFormatOrder['sku_id']            = empty($arrOrder['sku_id']) ? '' : strval($arrOrder['sku_id']);
            $arrFormatOrder['sku_name']          = empty($arrOrder['sku_name']) ? '' : strval($arrOrder['sku_name']);
            $arrFormatOrder['upc_id']            = empty($arrOrder['upc_id']) ? '' : strval($arrOrder['upc_id']);
            $arrFormatOrder['upc_unit']          = empty($arrOrder['upc_unit']) ? '' : strval($arrOrder['upc_unit']);
            $arrFormatOrder['sku_net']           = empty($arrOrder['sku_net']) ? '' : strval($arrOrder['sku_net']);
            $arrFormatOrder['sku_net_unit']      = empty($arrOrder['sku_net_unit']) ? '' : strval($arrOrder['sku_net_unit']);
            $arrFormatOrder['sku_net_unit_text'] =
                Nscm_Define_Sku::SKU_NET_UNIT_TEXT[intval($arrFormatOrder['sku_net_unit'])] ?? '';
            $arrFormatOrder['unit_price_tax']    = empty($arrOrder['unit_price_tax']) ? '' : Nscm_Service_Price::convertDefaultToYuan($arrOrder['unit_price_tax']);
            $arrFormatOrder['unit_price']        = empty($arrOrder['unit_price']) ? '' : Nscm_Service_Price::convertDefaultToYuan($arrOrder['unit_price']);

            if (empty($arrFormatOrder['info'])) {
                $arrFormatOrder['info'] = [];
            }

            $arrInfo                  = [];
            $arrInfo['adjust_amount'] = empty($arrOrder['adjust_amount']) ? '' : strval($arrOrder['adjust_amount']);

            $arrInfo['production_or_expire_time'] = '';
            if (!empty($arrOrder['production_time'])) {
                $arrInfo['production_or_expire_time'] = $arrOrder['production_time'];
            } else if (!empty($arrOrder['expire_time'])) {
                $arrInfo['production_or_expire_time'] = $arrOrder['expire_time'];
            }

            $arrInfo['is_defective']      = $arrOrder['is_defective'];
            $arrInfo['is_defective_text'] = Nscm_Define_Stock::QUALITY_TEXT_MAP[$arrOrder['is_defective']];

            $arrInfo['location_code'] = $arrOrder['location_code'];

            if (empty($mapSku2Info[$arrOrder['sku_id']])) {
                $mapSku2Info[$arrOrder['sku_id']]                         = [];
                $arrFormatResult['stock_adjust_order_detail']['detail'][] = $arrFormatOrder;
            }

            $mapSku2Info[$arrOrder['sku_id']][] = $arrInfo;
        }

        foreach ($arrFormatResult['stock_adjust_order_detail']['detail'] as &$arrDetail) {
            $intSkuId = $arrDetail['sku_id'];
            if (empty($mapSku2Info[$intSkuId])) {
                $arrDetail['info'] = [];
            } else {
                $arrDetail['info'] = $mapSku2Info[$intSkuId];
            }
        }

        return $arrFormatResult;
    }
}
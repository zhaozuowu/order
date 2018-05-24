<?php
/**
 * @name Action_GetForzenSku
 * @desc 查询冻结单明细
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetFrozenSku extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stock_frozen_order_id'     => 'regex|patern[/^(F\d{13})?$/]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_Frozen_GetFrozenSku
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertString2Array('warehouse_ids');
        $this->objPage = new Service_Page_Frozen_GetFrozenSku();
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
            $arrFormatOrderSku = [];
            $intSkuId = $arrOrder['sku_id'];
            if(empty($intSkuId)) {
                continue;
            }

            $arrFormatOrder['stock_frozen_order_id'] =
                empty($arrOrder['stock_frozen_order_id']) ? '' : Nscm_Define_OrderPrefix::F . strval($arrOrder['stock_frozen_order_id']);
            $arrFormatOrder['sku_id'] = empty($arrOrder['sku_id']) ? '' : strval($arrOrder['sku_id']);
            $arrFormatOrderSku['storage_location_id'] = empty($arrOrder['storage_location_id']) ? '' : strval($arrOrder['storage_location_id']);
            $arrFormatOrderSku['origin_frozen_amount'] = empty($arrOrder['origin_frozen_amount']) ? '' : strval($arrOrder['origin_frozen_amount']);
            $arrFormatOrderSku['current_frozen_amount'] = empty($arrOrder['current_frozen_amount']) ? '' : strval($arrOrder['current_frozen_amount']);
            $arrFormatOrderSku['is_defective'] = empty($arrOrder['is_defective']) ? '' : $arrOrder['is_defective'];
            $arrFormatOrderSku['is_defective_text']   =
                empty($arrOrder['is_defective']) ? '' : Nscm_Define_Stock::QUALITY_TEXT_MAP[$arrOrder['is_defective']];

            $arrFormatOrderSku['production_or_expire_time'] = empty($arrOrder['sku_valid_time']) ? '' : $arrOrder['sku_valid_time'];
            $arrFormatOrder['sku_name'] = empty($arrOrder['sku_name']) ? '' : strval($arrOrder['sku_name']);
            $arrFormatOrder['upc_id'] = empty($arrOrder['upc_id']) ? '' : strval($arrOrder['upc_id']);
            $arrFormatOrder['sku_net'] = empty($arrOrder['sku_net']) ? '' : strval($arrOrder['sku_net']);
            $arrFormatOrder['sku_net_unit'] = empty($arrOrder['sku_net_unit']) ? '' : strval($arrOrder['sku_net_unit']);
            $arrFormatOrder['sku_net_unit_text'] =
                Nscm_Define_Sku::SKU_NET_UNIT_TEXT[intval($arrFormatOrder['sku_net_unit'])] ?? '';

            $arrFormatOrder['upc_unit'] = empty($arrOrder['min_upc']['upc_unit']) ? '' : $arrOrder['min_upc']['upc_unit'];
            $arrFormatOrder['upc_unit_num'] = empty($arrOrder['min_upc']['upc_unit_num']) ? '' : $arrOrder['min_upc']['upc_unit_num'];
            $arrFormatOrder['upc_unit_text'] = empty($arrOrder['min_upc']['upc_unit']) ? '' : Nscm_Define_Sku::UPC_UNIT_MAP[$arrOrder['min_upc']['upc_unit']];

            if(empty($arrFormatResult['list'][$intSkuId])) {
                $arrFormatResult['list'][$intSkuId] = $arrFormatOrder;
            }
            $arrFormatResult['list'][$intSkuId]['detail'][] = $arrFormatOrderSku;
        }

        $arrFormatResult['list'] = array_values($arrFormatResult['list']);
        return $arrFormatResult;
    }
}
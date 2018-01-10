<?php
/**
 * @name Action_GetSkuStockInfo
 * @desc 查询商品库存信息
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetSkuStockInfo extends Order_Base_Action
{
    protected $boolCheckLogin = false;
    protected $boolCheckAuth = false;
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id'     => 'int|required',
        'sku_ids'          => 'arr|required|arr_min[1]|type[int]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_adjust_GetStockInfo
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_adjust_GetStockInfo();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrFormatResult = [
        ];

        if(empty($data)) {
            return $arrFormatResult;
        }

        foreach ($data as $value) {
            $arrFormatDetail = [];
            $arrFormatDetail['sku_id'] = empty($value['sku_id']) ? '' : intval($value['sku_id']);
            $arrFormatDetail['cost_unit_price'] = empty($value['cost_unit_price']) ? '' : intval($value['cost_unit_price']);
            $arrFormatDetail['cost_unit_price_tax'] = empty($value['cost_unit_price_tax']) ? '' : intval($value['cost_unit_price_tax']);
            $arrFormatDetail['available_amount'] = empty($value['available_amount']) ? '' : intval($value['available_amount']);

            $arrFormatResult[] = $arrFormatDetail;
        }

        return $arrFormatResult;
    }
}
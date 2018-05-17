<?php
/**
 * @name Action_GetReserveOrderSkuByOrderSku
 * @desc 根据预约单号和商品编码/条码查询商品信息
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderSkuByOrderSku extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'regex|patern[/^ASN\d{13}$/]',
        'sku_upc_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct function
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Reserve_GetReserveOrderSkuByOrderSku();
    }

    /**
     * format result, output data format process
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [];
        if (!empty($arrRet)) {
            $arrRoundResult = [];
            $arrRoundResult['upc_id'] = empty($arrRet['upc_id'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['upc_id']);
            $arrRoundResult['sku_id'] = empty($arrRet['sku_id'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['sku_id']);
            $arrRoundResult['sku_name'] = empty($arrRet['sku_name'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['sku_name']);
            $arrRoundResult['upc_unit_text'] =
                isset(Order_Define_Sku::UPC_UNIT_MAP[intval($arrRet['upc_unit'])])
                    ? Order_Define_Sku::UPC_UNIT_MAP[intval($arrRet['upc_unit'])]
                    : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['upc_min_unit'] = intval($arrRet['upc_min_unit']);
            $arrRoundResult['upc_min_unit_text'] =
                isset(Order_Define_Sku::UPC_UNIT_MAP[intval($arrRet['upc_min_unit'])])
                    ? Order_Define_Sku::UPC_UNIT_MAP[intval($arrRet['upc_min_unit'])]
                    : Order_Define_Const::DEFAULT_EMPTY_RESULT_STR;
            $arrRoundResult['upc_unit_num'] = empty($arrRet['upc_unit_num'])
                ? 0 : intval($arrRet['upc_unit_num']);
            $arrRoundResult['reserve_order_sku_plan_amount'] = empty($arrRet['reserve_order_sku_plan_amount'])
                ? 0 : intval($arrRet['reserve_order_sku_plan_amount']);
            $arrRoundResult['sku_main_image'] = empty($arrRet['sku_main_image'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['sku_main_image']);
            $arrRoundResult['abandon_time'] =
                Order_Util::getFormatDateTime($arrRet['abandon_time']);
            $arrRoundResult['product_expire_time'] =
                Order_Util::getFormatDateTime($arrRet['product_expire_time']);

            $arrFormatResult = $arrRoundResult;
        }

        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }
}
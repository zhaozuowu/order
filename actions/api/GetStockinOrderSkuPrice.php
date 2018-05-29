<?php
/**
 * @name Action_GetStockinOrderSkuPrice
 * @desc 获取入库单sku成本价
 * @author hang.song02@ele.me
 */

class Action_GetStockinOrderSkuPrice extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'order_id' => 'int|required',
        'sku_id' => 'int|required',
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
        $this->objPage = new Service_Page_Stockin_GetStockinOrderSkuPrice();
    }

    /**
     * format result, output data format process
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormat['sku_price'] = $arrRet['sku_price'];
        $arrFormat['sku_price_tax'] = $arrRet['sku_price_tax'];
        return $arrFormat;
    }
}
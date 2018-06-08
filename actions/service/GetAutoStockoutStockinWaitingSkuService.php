<?php
/**
 * @name Action_GetAutoStockoutStockinWaitingSkuApi
 * @desc query warehouse waiting skus
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_Service_GetAutoStockoutStockinWaitingSkuService extends Order_Base_ServiceAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id' => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_Stockin_GetAutoStockoutStockinWaitingSku
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockin_GetAutoStockoutStockinWaitingSku();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrRet = [];
        foreach ((array)$data as $row) {
            $arrRet[] = [
                'stockin_order_id' => Nscm_Define_OrderPrefix::SIO . $row['stockin_order_id'],
                'sku_id' => intval($row['sku_id']),
                'sku_plan_amount' => intval($row['reserve_order_sku_plan_amount']),
            ];
        }
        return $arrRet;
    }
}
<?php
/**
 * @name Service_Page_Purchase_CreatePurchaseOrder
 * @desc Service_Page_Purchase_CreatePurchaseOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Purchase_CreatePurchaseOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataPurchase;

    /**
     * Service_Page_Purchase_CreatePurchaseOrder constructor.
     */
    function __construct()
    {
        $this->objDataPurchase = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $arrPurchase = $arrInput;
        $arrRes = $this->objDataPurchase->saveCreatePurchaseOrder($arrPurchase);
        $strKey = $arrRes['key'];
        $intPurchaseOrderId = $arrRes['purchase_order_id'];
        $this->objDataPurchase->sendPurchaseInfoToWmq($strKey);
        $arrRet = [
            'purchase_order_id' => $intPurchaseOrderId,
        ];
        return $arrRet;
    }
}
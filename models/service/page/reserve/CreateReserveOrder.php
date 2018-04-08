<?php
/**
 * @name Service_Page_Reserve_CreateReserveOrder
 * @desc Service_Page_Reserve_CreateReserveOrder
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Reserve_CreateReserveOrder implements Order_Base_Page
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataReserve;

    /**
     * Service_Page_Reserve_CreateReserveOrder constructor.
     */
    function __construct()
    {
        $this->objDataReserve = new Service_Data_Reserve_ReserveOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrReserve = $arrInput;
        try {
            $arrRes = $this->objDataReserve->saveCreateReserveOrder($arrReserve);
            $strKey = $arrRes['key'];
            $intReserveOrderId = $arrRes['purchase_order_id'];
            $this->objDataReserve->sendReserveInfoToWmq($strKey);
        } catch (Order_BusinessError $e) {
            if (Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED == $e->getCode()) {
                $intReserveOrderId = $e->getArrArgs()['reserve_order_id'];
            } else {
                throw $e;
            }
        }
        $arrRet = [
            'purchase_order_id' => $intReserveOrderId,
        ];
        return $arrRet;
    }
}
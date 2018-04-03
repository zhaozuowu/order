<?php
/**
 * @name Dao_Ral_Oms
 * @desc oms ral dao
 * @author lvbochao(bochao.lv@ele.me)
 */

class Dao_Ral_Oms extends Order_ApiRaler
{
    /**
     * api raler
     * @var Order_ApiRaler
     */
    protected $objApiRal;

    /**
     * update oms order info
     * @var string
     */
    const API_RALER_UPDATE_OMS_ORDER_INFO = 'omsuiupdateomsorderinfo';

    /**
     * delivery order
     * @var string
     */
    const API_RALER_DELIVERY_ORDER = 'omsdeliveryorder';

    /**
     * update oms order info
     * @param int $intBusinessOrderId
     * @param int $intStockoutOrderId
     * @param int $intShipmentOrderId
     * @param array[] $arrOmsSku
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function updateOmsOrderInfo($intBusinessOrderId, $intStockoutOrderId, $intShipmentOrderId, $arrOmsSku)
    {
        $arrOrderInfo = [
            0 => [
                'parent_order_id' => $intBusinessOrderId,
                'parent_key' => -1,
                'order_id' => $intStockoutOrderId,
                'order_type' => Nscm_Define_OmsOrder::NWMS_ORDER_TYPE_STOCK_OUT,
                'skus' => $arrOmsSku,
            ],
            1 => [
                'parent_order_id' => $intStockoutOrderId,
                'parent_key' => 0,
                'order_id' => $intShipmentOrderId,
                'order_type' => Nscm_Define_OmsOrder::TMS_ORDER_TYPE_SHIPMENT,
                'skus' => $arrOmsSku,
            ],
        ];

        $req[self::API_RALER_UPDATE_OMS_ORDER_INFO] = [
            'order_info' => Nscm_Lib_Util::jsonEncode($arrOrderInfo),
        ];
        Bd_Log::debug('ral input params: ' . json_encode($req));
        $ret = $this->getData($req);
        Bd_Log::debug('ral output result: ' . json_encode($ret));
        $ret = $ret[self::API_RALER_UPDATE_OMS_ORDER_INFO] ?? [];
        return $ret;
    }

    /**
     * delivery order
     * @param int $stockoutOrderId
     * @param array $arrSkuInfo
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function deliveryOrder($stockoutOrderId, $arrSkuInfo)
    {
        $arrOrderInfo = [
            'stockout_order_id' => $stockoutOrderId,
            'sku_info' => $arrSkuInfo,
        ];
        $req[self::API_RALER_DELIVERY_ORDER] = $arrOrderInfo;
        Bd_Log::debug('ral input params: ' . json_encode($req));
        $ret = $this->getData($req);
        Bd_Log::debug('ral output result: ' . json_encode($ret));
        $ret = $ret[self::API_RALER_UPDATE_OMS_ORDER_INFO] ?? [];
        return $ret;
    }

    /**
     * @param $data
     * @return array
     * @throws Order_BusinessError
     */
    public function omsuiupdateomsorderinfo($data){
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
            Order_BusinessError::throwException(Order_Error_Code::ERR__RAL_ERROR);
            return [];
        } else {
            return $data['result'];
        }
    }

    /**
     * @param $data
     * @return array
     * @throws Order_BusinessError
     */
    public function omsdeliveryorder($data) {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
            Order_BusinessError::throwException(Order_Error_Code::ERR__RAL_ERROR);
            return [];
        } else {
            return $data['result'];
        }
    }
}

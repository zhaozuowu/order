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
     * update oms order info
     * @param $intOmsOrderId
     * @param $intStockoutOrderId
     * @param $intShipmentOrderId
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function updateOmsOrderInfo($intOmsOrderId, $intStockoutOrderId, $intShipmentOrderId)
    {
        $req[self::API_RALER_UPDATE_OMS_ORDER_INFO] = [
            'oms_order_id' => $intOmsOrderId,
            'stockout_order_id' => $intStockoutOrderId,
            'shipment_order_id' => $intShipmentOrderId,
        ];
        Bd_Log::debug('ral input params: ' . json_encode($req));
        $ret = $this->getData($req);
        Bd_Log::debug('ral output result: ' . json_encode($ret));
        $ret = !empty($ret[self::API_RALER_UPDATE_OMS_ORDER_INFO]) ? $ret[self::API_RALER_UPDATE_OMS_ORDER_INFO] : [];
        return $ret;
    }

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
}

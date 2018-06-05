<?php
/**
 * @name Service_Data_Rf_Barcode
 * @desc Barcode service data
 * @author chenwende@iwaimai.baidu.com
 */
class Service_Data_Rf_Barcode
{
    /**
     * @param $strOrderId
     * @param int $intLineHeight
     * @param int $intMinLineWidth
     * @return string
     * @throws Order_BusinessError
     */
    public function getBarcodeImg($strOrderId, $intLineHeight = 200, $intMinLineWidth = 1)
    {
        if (empty($strOrderId)
            || (0 >= $intLineHeight)
            || (0 >= $intMinLineWidth)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }

        return Order_Util_BarcodeUtil::generateBarcodeImg($strOrderId, $intLineHeight, $intMinLineWidth);
    }
}

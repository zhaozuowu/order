<?php
/**
 * @name Service_Page_Rf_GetBarcodeImg
 * @desc Service_Page_Rf_GetBarcodeImg
 * @author chenwende@iwaimai.baidu.com
 */
class Service_Page_Rf_GetBarcodeImg implements Order_Base_Page
{
    /**
     * @var Service_Data_Rf_Barcode
     */
    private $objData;

    /**
     * 限制的传入文本长度
     */
    const MAX_ORDER_ID_LENGTH = 256;

    /**
     * Service_Page_Rf_GetBarcodeImg constructor.
     */
    function __construct()
    {
        $this->objData = new Service_Data_Rf_Barcode();
    }

    /**
     * execute
     * @param array $arrInput
     * @return int
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $strOrderId = strval($arrInput['order_id']);
        $intLineHeight = intval($arrInput['line_height']);
        $intMinLineWidth = intval($arrInput['min_line_width']);
        // 如果传入的参数过长，返回错误
        if (self::MAX_ORDER_ID_LENGTH < strlen($strOrderId)) {
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        // 如果传入的参数内容为空则传入默认的图像尺寸参数
        if (empty($intLineHeight)) {
            $intLineHeight = 50;
        }
        if (empty($intMinLineWidth)) {
            $intMinLineWidth = 1;
        }

        return $this->objData->getBarcodeImg($strOrderId, $intLineHeight, $intMinLineWidth);
    }
}
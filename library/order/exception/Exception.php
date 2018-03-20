<?php
/**
 * Order_Exception_Exception
 * User: bochao.lv
 * Date: 2018/3/9
 * Time: 14:42
 */

/**
 * Class Order_Exception_Exception
 * @property-read int $order_id
 * @property-read int $sku_id
 * @property-read string $sku_name
 * @property-read int $exception_type
 * @property-read int $exception_type_concrete
 * @property-read int $exception_level
 * @property-read string $exception_info
 * @property-read int $exception_time
 */
class Order_Exception_Exception
{
    /**
     * exception info
     * @var array
     */
    private $arrInfo = [
        'order_id' => 0,
        'sku_id' => 0,
        'sku_name' => '',
        'exception_type' => 0,
        'exception_type_concrete' => 0,
        'exception_level' => 0,
        'exception_info' => '',
        'exception_time' => 0,
    ];

    /**
     * Order_Exception_Exception constructor.
     * @param int $intOrderId
     * @param int $intSkuId
     * @param string $strSkuName
     * @param int $intExceptionTypeConcrete
     * @param int $intExceptionLevel
     * @param string $strExceptionInfo
     */
    function __construct($intOrderId,
                         $intSkuId,
                         $strSkuName,
                         $intExceptionTypeConcrete,
                         $intExceptionLevel,
                         $strExceptionInfo = '')
    {
        $intOrderId = intval($intOrderId);
        $intSkuId = intval($intSkuId);
        $strSkuName = strval($strSkuName);
        $intExceptionTypeConcrete = intval($intExceptionTypeConcrete);
        $intExceptionType = Order_Exception_Const::MAP_TYPE[$intExceptionTypeConcrete];
        if (empty($intExceptionType)) {
            trigger_error('type concrete error', E_USER_ERROR);
        }
        $intExceptionLevel = intval($intExceptionLevel);
        $strExceptionInfo = Order_Exception_Const::MAP_TEXT[$intExceptionTypeConcrete];
        $intExceptionTime = time();
        $this->arrInfo['order_id'] = $intOrderId;
        $this->arrInfo['sku_id'] = $intSkuId;
        $this->arrInfo['sku_name'] = $strSkuName;
        $this->arrInfo['exception_type'] = $intExceptionType;
        $this->arrInfo['exception_type_concrete'] = $intExceptionTypeConcrete;
        $this->arrInfo['exception_level'] = $intExceptionLevel;
        $this->arrInfo['exception_info'] = $strExceptionInfo;
        $this->arrInfo['exception_time'] = $intExceptionTime;
    }

    /**
     * get db array
     * @return array
     */
    public function getDbArray()
    {
        return $this->arrInfo;
    }

    /**
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        return $this->arrInfo[$name];
    }

    /**
     * set order id
     * @param $intOrderId
     */
    public function setOrderId($intOrderId)
    {
        $intOrderId = intval($intOrderId);
        $this->arrInfo['order_id'] = $intOrderId;
    }
}
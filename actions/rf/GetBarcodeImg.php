<?php
/**
 * @name Action_GetBarcodeImg
 * @desc rf获取条形码图像（返回图像输出流）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetBarcodeImg extends Order_Base_Action
{
    /**
     * @var bool 检查用户登录
     */
    protected $boolCheckLogin = false;

    /**
     * @var bool 校验用户权限
     */
    protected $boolCheckAuth = false;

    /**
     * @var bool 检查ip权限
     */
    protected $boolCheckIp = false;

    /**
     * @var bool 校验仓库权限
     */
    protected $boolCheckWarehouse = false;

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'order_id' => 'str|required',
        'line_height' => 'int',
        'min_line_width' => 'int',
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
        $this->objPage = new Service_Page_Rf_GetBarcodeImg();
    }

    /**
     * format result, output data format process
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        return $arrRet;
    }

    /**
     * @param string $display
     */
    protected function response($display = 'json')
    {
        header('Content-Type: image/png');
        echo $this->arrData['result'];
    }
}
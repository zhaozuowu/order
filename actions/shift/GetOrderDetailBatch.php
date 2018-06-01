<?php
/**
 * Class Action_GetOrderDetailBatch
 */

class Action_GetOrderDetailBatch extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'shift_order_ids'        => 'arr|required',
        'page_num'              => 'int|optional|default[1]',
        'page_size'             => 'int|optional|default[50]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * @var
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertString2Array('shift_order_ids');
        $this->objPage = new Service_Page_Shift_GetOrderDetailBatch();
    }

    /**
     * 将逗号分隔字符串转换为数组
     * @param string $strKey
     */
    protected function convertString2Array($strKey) {
        if ($this->intMethod == Order_Define_Const::METHOD_GET) {
            if(!empty($this->arrReqGet[$strKey])) {
                $this->arrReqGet[$strKey] = explode(',', $this->arrReqGet[$strKey]);
            }
        } else if ($this->intMethod == Order_Define_Const::METHOD_POST) {
            if(!empty($this->arrReqPost[$strKey])) {
                $this->arrReqPost[$strKey] = explode(',', $this->arrReqPost[$strKey]);
            }
        }
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
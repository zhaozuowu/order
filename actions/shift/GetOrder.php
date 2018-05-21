<?php
/**
 * @name Action_GetOrder
 * @desc 查询库存调整单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids'             => 'arr|required',
        'shift_order_id'            => 'regex|patern[/^(S\d{13})?$/]',
        'status'                    => 'int|optional',
        'source_location'           => 'str|optional',
        'target_location'           => 'str|optional',
        'sku_id'                    => 'str|optional',
        'sku_name'                  => 'str|optional',
        'begin_date'                => 'int|default[0]',
        'end_date'                  => 'int|default[0]',
        'page_num'                  => 'int|optional|default[1]',
        'page_size'                 => 'int|optional|default[50]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * page service
     * @var Service_Page_Adjust_GetOrder
     */
    protected $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->convertString2Array('warehouse_ids');
        $this->objPage = new Service_Page_Shift_GetOrder();
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
<?php
/**
 * @name Action_GetLogList
 * @desc 查询出库单日志
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_GetLogList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_id' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_GetLogList();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $ret = ['log_list'=>[]];
        if (empty($data)) {
            return $ret;
        }
        $arrFormatRet = [];
        foreach((array)$data as $arrRetItem) {
            $arrFormatRetItem = [];
            $arrFormatRetItem['create_time'] = empty($arrRetItem['create_time']) ?  '': date('Y-m-d H:i:s',$arrRetItem['create_time']);
            $arrFormatRetItem['operator'] = empty($arrRetItem['operator_name']) ? '' : $arrRetItem['operator_name'];
            $arrFormatRetItem['remarkre'] = empty($arrRetItem['content']) ? '' : $arrRetItem['content'];
            $arrFormatRet['loglist'][] = $arrFormatRetItem;
        }
        return $arrFormatRet;
    }

}
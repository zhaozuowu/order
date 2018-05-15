<?php
/**
 * @name SaveOrderOperateRecord.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/15 21:15
 */

class Action_SaveOrderOperateRecord extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'order_id' => 'regex|patern[/^(SIO|ASN)\d{13}$/]',
        'device' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Main_SaveOrderOperateRecord();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return ['result' => boolval($data)];
    }
}
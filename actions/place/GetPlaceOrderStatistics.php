<?php
/**
 * @desc 获取上架单状态统计
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */


class Action_GetPlaceOrderStatistics extends Order_Base_Action
{

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
        $this->arrInputParams =[];
        $this->objPage = new Service_Page_Place_GetPlaceOrderStatistics();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {

        $ret = [];
        if (empty($data)) {
            return $ret;
        }
        return $data;
        //$arrFormatRet

    }

}
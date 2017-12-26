<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_GetOrder extends Nscm_Base_Action {

    protected $boolCheckLogin = false; //todo: debug
    protected $boolCheckAuth = false;  //todo: debug

    /**
     * get order
     * @var Service_Page_Adjust_GetOrder
     */
    protected $objGetOrder = null;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->objGetOrder = new Service_Page_Adjust_GetOrder();
    }

    /**
     * 加载validate规则
     */
    public function beforeValidate()
    {
        $this->objValidator
            ->addValidator('warehouse_ids', $this->arrReqPost['warehouse_ids'], 'arr|required|arr_min[1]|type[int]', 'warehouse_ids输入有误');
        $this->objValidator
            ->addValidator('stock_adjust_order_id', $this->arrReqPost['stock_adjust_order_id'], 'int|optional', 'stock_adjust_order_id输入有误');
        $this->objValidator
            ->addValidator('adjust_type', $this->arrReqPost['adjust_type'], 'int|optional', 'adjust_type输入有误');
        $this->objValidator
            ->addValidator('begin_date', $this->arrReqPost['begin_date'], 'int|optional', 'begin_date输入有误');
        $this->objValidator
            ->addValidator('end_date', $this->arrReqPost['end_date'], 'int|optional', 'end_date输入有误');
        $this->objValidator
            ->addValidator('page_num', $this->arrReqPost['page_num'], 'int|default[1]', 'page_num输入有误');
        $this->objValidator
            ->addValidator('page_size', $this->arrReqPost['page_size'], 'int|default[20]', 'page_size输入有误');

    }

    /**
     * do execute
     * @return array
     */
    public function myExecute()
    {
        $arrPageInfo = $this->objGetOrder->execute($this->arrFilterResult);
        return $arrPageInfo;
    }

    /**
     * format
     * @param  $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        return $arrRet;
    }
}
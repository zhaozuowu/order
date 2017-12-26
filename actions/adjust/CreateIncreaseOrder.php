<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Action_CreateIncreaseOrder extends Nscm_Base_Action {
    /**
     * create increase order
     * @var Service_Page_Adjust_CreateOrder
     */
    protected $objCreateIncreaseOrder = null;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->objCreateIncreaseOrder = new Service_Page_Adjust_CreateOrder();
    }

    /**
     * 加载validate规则
     */
    public function beforeValidate()
    {
        $this->objValidator
            ->addValidator('warehouse_id', $this->arrReqPost['warehouse_id'], 'int|required', 'warehouse_id输入有误');
        $this->objValidator
            ->addValidator('adjust_type', $this->arrReqPost['adjust_type'], 'int|required', 'adjust_type输入有误');
        $this->objValidator
            ->addValidator('remark', $this->arrReqPost['remark'], 'str|required', 'remark输入有误');
        $this->objValidator->
            addValidator('detail', $this->arrReqPost["detail"], 'arr|required|arr_min[1]|type[arr]', '商品调整详情输入有误');
    }

    /**
     * do execute
     * @return array
     */
    public function myExecute()
    {
        $this->arrFilterResult['detail'] = $this->checkDetails(
            $this->objValidator,
            $this->arrFilterResult['detail']);

        $arrPageInfo = $this->objCreateIncreaseOrder->execute($this->arrFilterResult);
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

    /**
     * checkDetails
     * @param  $objValidator
     * @param  $arrDetail
     * @return array
     */
    public static function checkDetails($objValidator, $arrDetail)
    {
        $arrResult = [];
        foreach ($arrDetail as $arrItem) {
            $objValidator->addValidator('sku_id', $arrItem["sku_id"], 'int|required|len[7]', 'sku_id输入有误:' . $arrItem["sku_id"]);
            $objValidator->addValidator('adjust_amount', $arrItem["adjust_amount"], 'int|required', 'adjust_amount输入有误:' . $arrItem["adjust_amount"]);
            $objValidator->addValidator('production_or_expire_time', $arrItem['production_or_expire_time'], 'str|required', 'production_or_expire_time输入有误');

            $arrResult[] = $objValidator->validate();
        }
        return $arrResult;
    }
}
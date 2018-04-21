<?php
/**
 * @name Service_Page_Frozen_Unfrozen
 * @desc 冻结单解冻
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Frozen_Unfrozen
{
    /**
     * @var Service_Data_Frozen_StockUnfrozenOrderDetail
     */
    protected $objUnfrozen;

    /**
     * init
     */
    public function __construct()
    {
        $this->objUnfrozen = new Service_Data_Frozen_StockUnfrozenOrderDetail();
    }

    /**
     * execute
     * @param $arrInput
     * @return mixed
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrInput['stock_frozen_order_id'] =
            intval(Order_Util::trimStockFrozenOrderIdPrefix($arrInput['stock_frozen_order_id']));
        $this->checkParam($arrInput);
        $arrOutput = $this->objUnfrozen->unfrozen($arrInput);
        return $arrOutput;
    }

    /**
     * 校验参数
     * @param $arrInput
     * @throws Order_BusinessError
     */
    protected function checkParam($arrInput)
    {
        if(empty($arrInput['detail'])) {
            Order_BusinessError::throwException(Order_Error_Code::NWMS_UNFROZEN_DETAIL_PARAM_EMPTY);
        }
    }
}

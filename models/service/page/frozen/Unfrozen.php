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
     * @return int
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrOutput = $this->objUnfrozen->unfrozen($arrInput);
        return $arrOutput;
    }
}

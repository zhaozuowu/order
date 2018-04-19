<?php
/**
 * @name Service_Page_frozen_GetStockFrozenInfo
 * @desc 获取仓库商品可冻结批次数据
 * @author ziliang.zhang02@ele.me
 */

class Service_Page_frozen_GetStockFrozenInfo
{
    /**
     * frozen order data service
     * @var Service_Data_Stock
     */
    protected $objStock;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStock = new Service_Data_Stock();
    }

    /**
     * execute
     * @param $arrInput
     * @return array
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrOutput = $this->objStock->getStockFrozenInfo(
            $arrInput['warehouse_id'],
            $arrInput['sku_id'],
            $arrInput['is_defective']
        );

        return $arrOutput;
    }
}
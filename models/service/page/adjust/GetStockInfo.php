<?php
/**
 * @name Service_Page_adjust_GetStockInfo
 * @desc 查询库存信息，包括可用库存、成本价
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_adjust_GetStockInfo
{
    /**
     * adjust order data service
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
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $arrOutput = $this->objStock->getStockPeriodStock($arrInput['warehouse_id'], $arrInput['sku_ids']);
        return $arrOutput;
    }
}
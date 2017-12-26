<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_CreateOrder
{
    /**
     * commodity data service
     * @var Service_Data_Commodity_Category
     */
    protected $objStockAdjustOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objStockAdjustOrder = new Service_Data_StockAdjustOrder();
    }

    /**
     * execute
     * @param  array $arrInput 参数
     * @return array
     */
    public function execute($arrInput)
    {
        $arrFormatInput = [
            'warehouse_id'   => strval($arrInput['warehouse_id']),
            'warehouse_name' => strval($arrInput['warehouse_name']),
        ];

        $arrOutput = $this->objStockAdjustOrder->create($arrFormatInput);
        return $arrOutput;
    }
}

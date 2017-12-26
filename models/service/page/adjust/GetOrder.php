<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Service_Page_Adjust_GetOrder
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
            'warehouse_id'   => ['in', $arrInput['warehouse_ids']],
        ];
        if(isset($arrInput['stock_adjust_order_id'])) {
            $arrFormatInput['stock_adjust_order_id'] = $arrInput['stock_adjust_order_id'];
        }
        if(isset($arrInput['adjust_type'])) {
            $arrFormatInput['adjust_type'] = $arrInput['adjust_type'];
        }
        if(isset($arrInput['begin_date']) && isset($arrInput['end_date'])) {
            $arrFormatInput['create_time'] = ['between', [$arrInput['begin_date'], $arrInput['end_date'] + 3600]];
        }

        $orderBy = ['warehouse_id' => 'asc', 'create_time' => 'desc'];
        $offset = ($arrInput['page_num'] - 1) * $arrInput['page_size'];
        $limit = $arrInput['page_size'];

        $count = $this->objStockAdjustOrder->getCount($arrFormatInput);
        $arrOutput = $this->objStockAdjustOrder->get($arrFormatInput, $orderBy, $offset, $limit);

        return array('total' => $count, 'stock_adjust_order_list' => $arrOutput);
    }
}

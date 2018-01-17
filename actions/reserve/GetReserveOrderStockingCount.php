<?php
/**
 * @name Action_GetReserveOrderStockingCount
 * @desc 获取待入库预约单统计
 * @author zhaozuowu@iwaimai.baidu.com
 */

class Action_GetReserveOrderStockingCount extends Order_Base_Action
{
    protected $arrInputParams = [
        'warehouse_ids' => 'regex|patern[/^\d{7}(,\d{7})*$/]',
    ];

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
        $this->objPage = new Service_Page_Reserve_GetReserveOrderStockingCount();
    }

    /**
     * format result
     * @param int $intCount
     * @return array
     */
    public function format($intCount) {
        return [
            'count' => intval($intCount),
        ];
    }
}
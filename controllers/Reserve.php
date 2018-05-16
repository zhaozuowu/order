<?php
/**
 * @name Controller_Reserve
 * @desc Controller_Reserve
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Reserve extends Ap_Controller_Abstract {
    public $actions = array(
        'createreserveorder' => 'actions/reserve/CreateReserveOrder.php', #创建预约单#@skipped#
        'createreserveorderwrite' => 'actions/reserve/CreateReserveOrderWrite.php', #创建预约单写库（已废弃）API#@skipped#
        'destroyreserveorder' => 'actions/reserve/DestroyReserveOrder.php', #作废预约单（已废弃）API#@skipped#
        'getreserveorderlist' => 'actions/reserve/GetReserveOrderList.php', #获取采购订单列表（分页）#
//        'getreserveorderstatistics' => 'actions/reserve/GetReserveOrderStatistics.php', #查询采购单状态统计#@skipped#
        'getreserveorderdetail' => 'actions/reserve/GetReserveOrderDetail.php', #查询采购单详情#
        'getreserveorderskulist' => 'actions/reserve/GetReserveOrderSkuList.php', #查询采购单商品（分页）#
        'getreserveorderprintlist' => 'actions/reserve/GetReserveOrderPrintList.php', #预约入库单打印#
        'getreserveorderstockingcount' => 'actions/reserve/GetReserveOrderStockingCount.php', #查询待入库预约单数量#
        'getreserveorderskubyordersku' => 'actions/reserve/GetReserveOrderSkuByOrderSku.php', #根据预约单号和商品编码/条码查询商品信息#
    );
}

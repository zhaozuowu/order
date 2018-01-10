<?php

/**
 * @name Stockout_Controller
 * @desc stockout
 * @author nscm
 */
class Controller_Stockout extends Ap_Controller_Abstract
{
    public $actions = array(
        'deliveryorder' => 'actions/stockout/DeliveryOrder.php',//TMS完成揽收
        'finishorder' => 'actions/stockout/FinishOrder.php',//TMS完成门店签收
        'getstockoutbyid' => 'actions/stockout/GetStockoutById.php',
        'finishpickuporder' => 'actions/stockout/FinishPickupOrder.php',//仓库完成拣货
        'deliveryorder' => 'actions/stockout/DeliveryOrder.php',
        'createstockoutorder' => 'actions/stockout/CreateStockoutOrder.php',
        'getstockoutlist' => 'actions/stockout/getstockoutorderlist.php',
        'deletestockoutorder' => 'actions/stockout/DeleteStockoutOrder.php',//作废出库单
        'statistical' => 'actions/stockout/Statistical.php',//出库单状态统计
        'getloglist'  => 'actions/stockout/GetLogList.php',//查询出库单日志
        'getstockoutorderlist' => 'actions/stockout/GetStockoutOrderList.php',
        'deletestockoutorder' => 'actions/stockout/DeleteStockoutOrder.php', //作废出库单
        'getcancelstatus' => 'actions/stockout/GetCancelStatus.php', //查询出库单取消状态
        'getorderprintlist' => 'actions/stockout/GetOrderPrintList.php', #出库单分拣打印#
        'getskuprintlist' => 'actions/stockout/GetSkuPrintList.php', #出库单总拣打印#
        'getstockoutdetail'=>'actions/stockout/GetStockoutDetail.php'//销售出库明细
    );
}

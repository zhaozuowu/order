<?php

/**
 * @name Stockout_Controller
 * @desc stockout
 * @author nscm
 */
class Controller_Stockout extends Ap_Controller_Abstract
{
    public $actions = array(
        'deliveryorder' => 'actions/stockout/DeliveryOrder.php', #TMS完成揽收#@skipped#
        'finishorder' => 'actions/stockout/FinishOrder.php', #TMS完成门店签收#@skipped#
        'getstockoutbyid' => 'actions/stockout/GetStockoutById.php', #查询出库单明细#
        'finishpickuporder' => 'actions/stockout/FinishPickupOrder.php', #仓库完成拣货#
        'batchfinishpickuporder'=>'actions/stockout/BatchFinishPickupOrder.php',#仓库批量完成拣货#
        'deletestockoutorder' => 'actions/stockout/DeleteStockoutOrder.php', #作废出库单#
        'statistical' => 'actions/stockout/Statistical.php', #出库单状态统计#
        'getloglist'  => 'actions/stockout/GetLogList.php', #查询出库单日志#
        'getstockoutorderlist' => 'actions/stockout/GetStockoutOrderList.php', #查询出库单列表#
        'getcancelstatus' => 'actions/stockout/GetCancelStatus.php', #查询出库单取消状态#
        'getorderprintlist' => 'actions/stockout/GetOrderPrintList.php', #出库单分拣打印#
        'getskuprintlist' => 'actions/stockout/GetSkuPrintList.php', #出库单总拣打印#
        'getstockoutdetail'=>'actions/stockout/GetStockoutDetail.php', #销售出库明细#
        'getstockoutorderskus' => 'actions/stockout/GetStockoutOrderSkus.php', #查询出库单商品列表#
        'getdistributionskulist' => 'actions/stockout/GetDistributionSkuList.php', #查询配货商品列表#
        'getcustomerbyid' => 'actions/stockout/GetCustomerById.php', #查询客户信息#
        'getcustomernamesug' => 'actions/stockout/GetCustomernameSug.php', #查询客户名称sug#
        'createstockoutorder' => 'actions/stockout/CreateStockoutOrder.php', #手动创建出库单#
    );
}

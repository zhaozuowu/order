<?php
/**
 * @name Controller_Api
 * @desc 订单模块Api Controller_Api
 * @author chenwende@iwaimai.baidu.com
 */
class Controller_Api extends Ap_Controller_Abstract {
    public $actions = array(
        'getstockinreservedetailformapi' => 'actions/api/GetStockinReserveDetailFormApi.php', #报表章节：采购入库批次明细（分页）API#@skipped#
        'getstockoutstockindetailformapi' => 'actions/api/GetStockoutStockinDetailFormApi.php', #报表章节：销退入库明细（分页）API#@skipped#
        'getstockoutdetailformapi'=>'actions/api/GetStockoutDetailFormApi.php',  #报表章节：销售出库明细（分页）API#@skipped#
        'getorderdetailformapi' => 'actions/api/GetOrderDetailFormApi.php', #报表章节：库存调整明细（分页）API#@skipped#
        'getstockinstockoutorderinfoapi' => 'actions/api/GetStockinStockoutOrderInfoApi.php', #查询销退入库单（包括商品列表）详情API#@skipped#
        'getstockoutbyidsapi' => 'actions/api/GetStockoutByIdsApi.php', #查询出库单API#
        'getbusinessformorderbyids' => 'actions/api/GetBusinessFormOrderByIds.php', #查询业态订单API#
        'createstockinorderapi' => 'actions/api/CreateStockInOrderApi.php', #创建自动销退入库单#@skipped#
    );
}

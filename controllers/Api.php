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
    );
}

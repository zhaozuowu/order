<?php
/**
 * @name Controller_Reserve
 * @desc 报表-出入库 Controller_Reserve
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Statistics extends Ap_Controller_Abstract {
    public $actions = array(
        'getstockinreservedetailform' => 'actions/statistics/GetStockinReserveDetailForm.php', #报表章节：采购入库批次明细（分页）#
        'getstockoutstockindetailform' => 'actions/statistics/GetStockoutStockinDetailForm.php', #报表章节：销退入库明细（分页）#
    );
}

<?php
/**
 * @name Stockin_Controller
 * @desc stockin
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Stockin extends Ap_Controller_Abstract {
    public $actions = array(
        'createstockinorder' => 'actions/stockin/CreateStockinOrder.php', #创建入库单#
        'getstockinorderlist' => 'actions/stockin/GetStockinOrderList.php', #查询入库单列表（分页）#
        'getstockinstockoutorderlist' => 'actions/stockin/GetStockinOrderList.php', #查询销退入库单列表（分页）#
        'getstockinorderdetail' => 'actions/stockin/GetStockinOrderDetail.php', #查询入库单详情#
        'getstockinstockoutorderdetail' => 'actions/stockin/GetStockinOrderDetail.php', #查询销退入库单详情#
        'getstockinorderskulist' => 'actions/stockin/GetStockinOrderSkuList.php', #查询入库单商品（分页）#
        'getstockinorderprintlist' => 'actions/stockin/GetStockinOrderPrintList.php', #入库单打印#
        'getstockinstockoutorderprintlist' => 'actions/stockin/GetStockinOrderPrintList.php', #销退入库单打印#
    );
}

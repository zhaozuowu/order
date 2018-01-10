<?php
/**
 * @name Stockin_Controller
 * @desc stockin
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Stockin extends Ap_Controller_Abstract {
    public $actions = array(
        'createstockinorder' => 'actions/stockin/CreateStockinOrder.php',
        'getstockinorderlist' => 'actions/stockin/GetStockinOrderList.php',
        'getstockinorderdetail' => 'actions/stockin/GetStockinOrderDetail.php',
        'getstockinorderskulist' => 'actions/stockin/GetStockinOrderSkuList.php',
        'getstockinoderprintlist' => 'actions/reserve/GetStockinOrderPrintList.php', //入库单打印
    );
}

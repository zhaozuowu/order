<?php
/**
 * @name Stockin_Controller
 * @desc stockin
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Stockin extends Ap_Controller_Abstract {
    public $actions = array(
        'createstockinorder' => 'actions/stockin/CreateStockinOrder.php', #创建入库单#
        'createstockinstockoutorder' => 'actions/stockin/CreateStockinStockoutOrder.php', #创建销退入库单#
        'getstockinorderlist' => 'actions/stockin/GetStockinOrderList.php', #查询入库单列表（分页）#
        'getstockinstockoutorderlist' => 'actions/stockin/GetStockinStockoutOrderList.php', #查询销退入库单列表（分页）#
        'getstockinorderdetail' => 'actions/stockin/GetStockinOrderDetail.php', #查询入库单详情#
        'getstockinstockoutorderdetail' => 'actions/stockin/GetStockinStockoutOrderDetail.php', #查询销退入库单详情#
        'getstockinorderskulist' => 'actions/stockin/GetStockinOrderSkuList.php', #查询入库单商品（分页）#
        'getstockinstockoutorderskulist' => 'actions/stockin/GetStockinStockoutOrderSkuList.php', #查询销退入库单商品（分页）#
        'getstockinorderskus' => 'actions/stockin/GetStockinOrderSkus.php', #查询入库单商品（不分页）#
        'getstockinorderprintlist' => 'actions/stockin/GetStockinOrderPrintList.php', #入库单打印#
        'getstockinstockoutorderprintlist' => 'actions/stockin/GetStockinStockoutOrderPrintList.php', #销退入库单打印#
        'confirmstockinorder' => 'actions/stockin/ConfirmStockinOrder.php', #销退入库单打印#
        'updatestockinorderisprint' => 'actions/stockin/UpdateStockinOrderIsPrint.php', #更新入库单为已打印#
        'getstockinskubyordersku' => 'actions/stockin/GetStockinSkuByOrderSku.php', #根据入库单号和商品编码/条码查询商品信息#
    );
}

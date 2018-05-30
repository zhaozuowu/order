<?php
/**
 * @name Rf.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/21 11:40
 */

class Controller_Rf extends Ap_Controller_Abstract {
    public $actions = array(
        'getreserveorderlist' => 'actions/reserve/GetReserveOrderList.php', #rf获取预约单列表#
        'getreserveorderdetail' => 'actions/reserve/GetReserveOrderDetail.php', #rf获取预约单详情#
        'getreserveorderskulist' => 'actions/reserve/GetReserveOrderSkuList.php', #rf获取预约单sku列表#,
        'getreserveorderskubyordersku' => 'actions/reserve/GetReserveOrderSkuByOrderSku.php', #rf根据预约单号和扫码查询商品信息#
        'saveorderoperaterecordstockin' => 'actions/main/SaveOrderOperateRecordStockin.php', #rf保存销退入库单操作纪录#
        'saveorderoperaterecordreserve' => 'actions/main/SaveOrderOperateRecordReserve.php', #rf保存预约单操作纪录#
        'getstockinstockoutorderlist' => 'actions/stockin/GetStockinStockoutOrderList.php', #rf查询销退入库单列表#
        'getstockinorderdetail' => 'actions/stockin/GetStockinOrderDetail.php', #rf获取入库单详情#
        'getstockinorderskulist' => 'actions/stockin/GetStockinOrderSkuList.php', #rf获取入库单商品列表#
        'getstockinskubyordersku' => 'actions/stockin/GetStockinSkuByOrderSku.php', #rf根据入库单号和扫码查询商品信息#,
        'createstockinorder' => 'actions/stockin/CreateStockinOrder.php', #rf创建预约入库单#
        'confirmstockinorder' => 'actions/stockin/ConfirmStockinOrder.php', #rf确认销退入库单#
    );
}

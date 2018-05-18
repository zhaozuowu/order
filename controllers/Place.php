<?php
/**
 * @desc 上架单
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Controller_Place extends Ap_Controller_Abstract {
    public $actions = array(
        'getplaceorderstatistics' => 'actions/place/GetPlaceOrderStatistics.php', #获取上架单状态统计#
        'confirmplaceorder' => 'actions/place/ConfirmPlaceOrder.php', #确认上架单#
        'createplaceorderbymanual' => 'actions/place/CreatePlaceOrderByManual.php', #手动创建上架单#
        'getplaceorderlist' => 'actions/place/GetPlaceOrderList.php', #获取上架单列表#
        'getplaceorderdetail' => 'actions/place/GetPlaceOrderDetail.php', #获取上架单详情#
        'getplaceorderprint' => 'actions/place/GetPlaceOrderPrint.php', #获取上架单打印列表#
    );
}
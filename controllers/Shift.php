<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Controller_Shift extends Ap_Controller_Abstract {
    public $actions = array(
        'createincreaseorder' => 'actions/shift/CreateOrder.php', #新建移位单#
        'createdecreaseorder' => 'actions/shift/CancelOrder.php', #取消移位单#
        'getorder' => 'actions/shift/GetOrder.php', #查询移位单#
        'getorderdetail' => 'actions/shift/GetOrderDetail.php', #查询移位单详情#
        'getlocationstock' => 'actions/shift/GetLocationStock.php', #查询库位库存信息#
    );
}
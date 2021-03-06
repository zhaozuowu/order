<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Controller_Shift extends Ap_Controller_Abstract {
    public $actions = array(
        'createorder' => 'actions/shift/CreateOrder.php', #新建移位单#
        'cancelorder' => 'actions/shift/CancelOrder.php', #取消移位单#
        'finishorder' => 'actions/shift/FinishOrder.php', #完成移位单#
        'getorder' => 'actions/shift/GetOrder.php', #查询移位单#
        'getorderdetail' => 'actions/shift/GetOrderDetail.php', #获取移位单详情#
        'getorderdetailbatch' => 'actions/shift/GetOrderDetailBatch.php', #批量获取移位单详情#
        'getlocationstock' => 'actions/shift/GetLocationStock.php', #查询库位库存信息#
    );
}
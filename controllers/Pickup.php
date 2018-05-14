<?php
/**
 * @name Controller_Pickup
 * @desc pick up controller
 * @author wanggang01@iwaimai.baidu.com
 */

class Controller_Pickup extends Ap_Controller_Abstract {
    /**
     * actions
     * @var array
     */
    public $actions = array(
        'getpickuporderlist' => 'actions/pickup/GetPickupOrderList.php', #获取拣货单分页#
        'getpickuporderdetail' => 'actions/pickup/GetPickupOrderDetail.php', #获取拣货单详情#
        'cancelpickuporder' => 'actions/pickup/CancelPickupOrder.php', #取消拣货单#
        'finishpickuporder' => 'actions/pickup/FinishPickupOrder.php', #拣货单完成拣货#
        'getpickuporderskulocation' => 'actions/pickup/GetPickupOrderSkuLocation.php', #获取sku库区库位#
    );
}

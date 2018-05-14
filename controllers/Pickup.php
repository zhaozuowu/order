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
        'getpickupordercountbywaiting' => 'actions/pickup/GetPickupOrderCountByWaiting.php', #获取待拣货状态拣货单数量#
        'getpickuporderprint' => 'actions/pickup/GetPickupOrderPrint.php', #获取拣货单打印#
    );
}

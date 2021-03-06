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
        'createpickuporder' => 'actions/pickup/CreatePickupOrder.php', #生成拣货单#
        'getpickuprowsprintlist' => 'actions/pickup/GetPickupRowsPrintList.php', #总拣单排线打印#
        'cancelpickuporder' => 'actions/pickup/CancelPickupOrder.php', #取消拣货单#
        'finishpickuporder' => 'actions/pickup/FinishPickupOrder.php', #拣货单完成拣货#
        'getpickuporderskulocation' => 'actions/pickup/GetPickupOrderSkuLocation.php', #获取sku库区库位#
        'gettmssnapshootnum' => 'actions/pickup/GetTmsSnapshootNum.php', #获取tms排线#
    );
}

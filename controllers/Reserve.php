<?php
/**
 * @name Controller_Reserve
 * @desc Controller_Reserve
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Reserve extends Ap_Controller_Abstract {
    public $actions = array(
        'createreserveorder' => 'actions/reserve/CreateReserveOrder.php',
        'createreserveorderwrite' => 'actions/reserve/CreateReserveOrderWrite.php',
        'destroyreserveorder' => 'actions/reserve/DestroyReserveOrder.php',
        'getreserveorderlist' => 'actions/reserve/GetReserveOrderList.php',
        'getreserveorderstatistics' => 'actions/reserve/GetReserveOrderStatistics.php',
        'getreserveeorderdetail' => 'actions/reserve/GetReserveOrderDetail.php',
    );
}

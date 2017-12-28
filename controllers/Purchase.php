<?php
/**
 * @name Controller_Purchase
 * @desc Controller_Purchase
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Purchase extends Ap_Controller_Abstract {
    public $actions = array(
        'createpurchaseorder' => 'actions/reserve/CreatePurchaseOrder.php',
        'getpurchaseorderlist' => 'actions/reserve/GetReserveOrderList.php',
        'getpurchaseorderstatistics' => 'actions/reserve/GetReserveOrderStatistics.php',
        'getpurchaseorderdetail' => 'actions/reserve/GetReserveOrderDetail.php',
        'createpurchaseorderwrite' => 'actions/reserve/CreatePurchaseOrderWrite.php',
        'destroypurchaseorder' => 'actions/reserve/DestroyPurchaseOrder.php',
    );
}

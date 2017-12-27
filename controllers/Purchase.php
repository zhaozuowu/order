<?php
/**
 * @name Controller_Purchase
 * @desc Controller_Purchase
 * @author lvbochao@iwaimai.baidu.com
 */
class Controller_Purchase extends Ap_Controller_Abstract {
    public $actions = array(
        'createpurchaseorder' => 'actions/purchase/CreatePurchaseOrder.php',
        'getpurchaseorderlist' => 'actions/purchase/GetPurchaseOrderList.php',
        'getpurchaseorderstatistics' => 'actions/purchase/GetPurchaseOrderStatistics.php',
    );
}

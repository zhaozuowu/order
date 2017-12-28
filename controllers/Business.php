<?php
/**
 * @name Controller_Business
 * @desc Controller_Business
 * @author jinyu02@iwaimai.baidu.com
 */
class Controller_Business extends Ap_Controller_Abstract {
    public $actions = array(
        'createbusinessformorder' => 'actions/business/CreateBusinessFormOrder.php',
        'getbusinessformorderlist' => 'actions/business/GetBusinessFormOrderList.php',//查询业态订单列表（分页）
        'getbusinessformorderbyid' => 'actions/business/GetBusinessFormOrderByid.php',//查询业态订单详情

    );
}
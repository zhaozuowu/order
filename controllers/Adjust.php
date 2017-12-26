<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Controller_Adjust extends Ap_Controller_Abstract {
    public $actions = array(
        'createincreaseorder' => 'actions/adjust/CreateIncreaseOrder.php',
        'getorder' => 'actions/adjust/GetOrder.php',
    );
}
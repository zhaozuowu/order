<?php
/**
 * @name Controller_Vendor
 * @desc vendor controller
 * @author wanggang01@iwaimai.baidu.com
 */

class Controller_Vendor extends Ap_Controller_Abstract {
    /**
     * actions
     * @var array
     */
    public $actions = array(
        'getvendorsugbyname' => 'actions/vendor/GetVendorSugByName.php',
    );
}

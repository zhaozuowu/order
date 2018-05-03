<?php
/**
 * @desc 上架单
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Controller_Place extends Ap_Controller_Abstract {
    public $actions = array(
        'getplaceorderstatistics' => 'actions/place/GetPlaceOrderStatistics.php', #获取上架单状态统计#
    );
}
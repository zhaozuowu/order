<?php
/**
 * @name Controller_Sku
 * @desc sku controller
 * @author wanggang01@iwaimai.baidu.com
 */

class Controller_Sku extends Ap_Controller_Abstract {
    /**
     * actions
     * @var array
     */
    public $actions = array(
        'getskulist' => 'actions/sku/GetSkuList.php', #查询全库sku不指定任何数据依赖限制(彩云支持沧海跨域访问)#@skipped#
    );
}

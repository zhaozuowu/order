<?php
/**
 * @name Controller_Frozen
 * @desc 冻结单
 * @author sunzhixin@iwaimai.baidu.com
 */

class Controller_Frozen extends Ap_Controller_Abstract {
    public $actions = array(
        'createfrozenorder' =>      'actions/frozen/CreateFrozenOrder.php', #创建冻结单#
        'unfrozen' =>               'actions/frozen/Unfrozen.php', #创建冻结单#
        'getorder' =>               'actions/frozen/GetOrder.php', #查询冻结单列表#
        'getorderbyid' =>           'actions/frozen/GetOrderById.php', #根据ID查询冻结单#
        'getfrozensku' =>           'actions/frozen/GetFrozenSku.php', #查询冻结单明细#
        'getunfrozendetail' =>      'actions/frozen/GetUnfrozenDetail.php', #查询冻结单解冻明细#
        'getstockfrozeninfo' =>  'actions/frozen/GetStockFrozenInfo.php', #获取仓库商品可冻结数据#
    );
}
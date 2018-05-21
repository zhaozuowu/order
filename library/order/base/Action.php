<?php
/**
 * @name Order_Base_Action
 * @desc Order_Base_Action
 * @author lvbochao@iwaimai.baidu.com
 */

abstract class Order_Base_Action extends Order_Base_BaseAction {
    /**
     * 校验仓库权限
     * @var bool
     */
    protected $boolCheckWarehouse = true;
//    protected $boolCheckLogin = false;
//    protected $boolCheckAuth = false;
}
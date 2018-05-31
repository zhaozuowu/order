<?php
/**
 * @name Main_Controller
 * @desc 主控制器,也是默认控制器
 * @author nscm
 */
class Controller_Main extends Ap_Controller_Abstract {
	public $actions = array(
		'getprintlist' => 'actions/GetPrintList.php',#打印（action不存在）#@skipped#
        'saveorderoperaterecord' => 'actions/main/SaveOrderOperateRecord.php', #保存单操作纪录#
	);
}

<?php
/**
 * @name Main_Controller
 * @desc 主控制器,也是默认控制器
 * @author nscm
 */
class Controller_Main extends Ap_Controller_Abstract {
	public $actions = array(
		'getprintlist' => 'actions/GetPrintList.php',
	);
}

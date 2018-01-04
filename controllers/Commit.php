<?php
/**
 * @name Commit_Controller
 * @desc wmq对应接口的控制器
 * @author nscm
 */
class Controller_Commit extends Ap_Controller_Abstract {
	public $actions = array(
		'recv' => 'actions/commit/Recv.php',
	);
}

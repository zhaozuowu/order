<?php
/**
 * @name Action_Recv
 * @desc Action_Recv
 * @author jinyu02@iwaimai.baidu.com
 */
class Action_Recv extends Order_Wmq_CommitAction {

    /**
     * execute
     * @return void
     */
    public function execute() {
        parent::execute();
    }

    /**
     * checkAuto
     * @return void
     */
    public function checkAuth() {
        parent::checkAuth();
    }
}
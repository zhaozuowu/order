<?php
/**
 * @name GenModel
 * @desc Orm生成
 * @author nscm
 */
Bd_Init::init();
$obj = new Wm_Orm_GenModel();
$obj->run();
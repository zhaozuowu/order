<?php
/**
 * @name index
 * @desc 入口文件
 * @author nscm
 */
$objApplication = Bd_Init::init();
$objResponse = $objApplication->bootstrap()->run();

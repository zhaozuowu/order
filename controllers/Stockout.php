<?php

/**
 * @name Stockout_Controller
 * @desc stockout
 * @author nscm
 */
class Controller_Stockout extends Ap_Controller_Abstract
{
    public $actions = array(

        'deliveryorder' => 'actions/stockout/DeliveryOrder.php',//TMS完成揽收
        'finishorder' => 'actions/stockout/FinishOrder.php',//TMS完成门店签收
        'getbusinessformorderlist' => 'actions/stockout/GetBusinessFormOrderList.php',//查询业态订单列表（分页）
        'getbusinessformorderbyid' => 'actions/business/GetBusinessFormOrderByid.php',//查询业态订单列表（分页）
        'createStockoutOrder' => 'actions/stockout/CreateStockoutOrder.php',
    );
}

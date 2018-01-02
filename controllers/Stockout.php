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
        'getstockoutbyid' => 'actions/stockout/GetStockoutById.php',
        'finishpickuporder' => 'actions/stockout/FinishPickupOrder.php',//仓库完成拣货
        'createstockoutorder' => 'actions/stockout/CreateStockoutOrder.php',
        'getstockoutlist' => 'actions/stockout/getstockoutorderlist.php',
    );
}

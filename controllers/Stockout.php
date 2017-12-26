<?php
/**
 * @name Stockout_Controller
 * @desc stockout
 * @author nscm
 */
class Controller_Stockout extends Ap_Controller_Abstract {
    public $actions = array(
        'deliveryorder' => 'actions/stockout/DeliveryOrder.php',
        'createStockoutOrder' => 'actions/stockout/CreateStockoutOrder.php',
    );
}

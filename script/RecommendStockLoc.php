<?php
Bd_Init::init();
ini_set('memory_limit','1G');
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

try{


//    $serviceName = new Bd_Wrpc_Client("bdwaimai_earthnet.nwms",'order','StockinService');
//
//    $objBaz = new Service_Page_Stockin_BatchCreateStockinOrderApi();
//    $rs = $objBaz->execute($param);
//    var_dump($rs);
//    exit();
    $param['requestParams']= [
        'ext_order_id'=>'1801180001809',
        'warehouse_id'=>'1000027',
        'details'=>[
            ['sku_id'=>'1000128','amount'=>'4'],
            ['sku_id'=>'1000225','amount'=>'4'],
        ]
    ];
    $serviceName = new Bd_Wrpc_Client("bdwaimai_earthnet.nwms",'stock','StockOutService');

    $objBaz = $serviceName->recommendStockLoc($param);
    var_dump($objBaz);
    exit();
    $serviceName = new Bd_Wrpc_Client("bdwaimai_earthnet.nwms",'order','StockinService');
    $objBaz = $serviceName->batchCreateStockaInOrder($param);
    var_dump($objBaz);

}catch(Bd_Wrpc_Exception_Sys $obwes){
    var_dump($obwes->getMessage());exit();
}catch(Bd_Wrpc_Exception_User $obwes){
    var_dump($obwes->getMessage());exit();
}catch(Exception $e){
    var_dump($e->getMessage());exit();
}

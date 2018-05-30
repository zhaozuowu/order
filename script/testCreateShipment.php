<?php
Bd_Init::init();
ini_set('memory_limit','1G');
try{

   $products = [
       [
           'skuId'=>'1000008',
           'name'=>'测试商品1',
           'amount'=>'2',
           'netWeight'=>'12',
           'netWeightUnit'=>'2',
           'upcUnit'=>'2',
           'specifications'=>1,
           'back'=>false,
           'eventype'=>0,
           ],
           [
               'skuId'=>'1000009',
               'name'=>'测试商品2',
               'amount'=>'2',
               'netWeight'=>'12',
               'netWeightUnit'=>'2',
               'upcUnit'=>'2',
               'specifications'=>1,
               'back'=>true,
               'eventype'=>2,
               ],
       [
           'skuId'=>'1000010',
           'name'=>'测试商品3',
           'amount'=>'2',
           'netWeight'=>'12',
           'netWeightUnit'=>'2',
           'upcUnit'=>'2',
           'specifications'=>1,
           'back'=>true,
           'eventype'=>2,
       ]
   ];

   $poi = [
     'longitude'=>'86.16629',
     'latitude'=>'41.777194',
     'address'=>'第一大道',
     'areaCode'=>'5976',
     'cityId'=>'5898',
     'cityName'=>'铁门关市',
     'districtId'=>'5976',
     'districtName'=>'铁门关市区',
     'coordsType'=>'1',
   ];
  //  $arrWarehouseRequest['backType'] =  false;
    $serviceName = new Bd_Wrpc_Client("scm.tms_core",'me.ele.scm.tms.oms.api.omsouter','WmsReferService');
    $param = ['user'=>(object)[],'warehouseRequest'=>['backType'=>false,'warehouseId' =>1000025,'businessType'=>1,'businessSubType'=>1,'businessJson'=>"{}",'orderRemark'=>'','orderNumber'=>2,'stockoutNumber'=>1801180001818,'requireReceiveStartTime'=>1526094458,'requireReceiveEndTime'=>1526180894,'products'=>$products,'userInfo'=>['npName'=>'a','npId'=>1,'contactName'=>'a','contactPhone'=>'a','customerServiceName'=>'王刚','customerServicePhone'=>'18513167728','poi'=>(object)$poi]]];

    print_r($param);exit();
    $objBaz = $serviceName->processWarehouseRequest($param);
#echo json_encode($param);exit();
    var_dump($objBaz);die();
    $content = base64_decode($objBaz['data']['content']);
    die;

    $fp = fopen('php://memory','r+');
    fputs($fp,$content);
    $zip = new ZipArchive;
    $fpp = fopen('php://memory','r+');
    $zip->open($fp);
    $zip->extractTo($fpp);
    rewind($fpp);
    echo fgets($fpp);
    die;
}catch(Bd_Wrpc_Exception_Sys $obwes){
    var_dump($obwes->getMessage());exit();
}catch(Bd_Wrpc_Exception_User $obwes){

}
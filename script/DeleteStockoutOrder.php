<?php

class DeleteStockoutOrder
{

    /**
     * Service_Data_StockoutOrder obj
     * @var Service_Data_StockoutOrder
     */
    protected $objData;

    /**
     * Service_Data_StockoutOrder constructor.
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();
        $this->objData = new Service_Data_StockoutOrder();
        $this->objOrmSku = new Model_Orm_StockoutOrderSku();

    }

    /**
     * 脚本执行入口
     */
    public function run()
    {
        try {
            $allowOrderStatus = [
                Order_Define_StockoutOrder::STAY_PICKING_STOCKOUT_ORDER_STATUS,
                Order_Define_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS,
                Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS,
            ];
            $condition = [
                'data_source' =>Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_OMS,
                'stockout_order_status'=>['in', $allowOrderStatus],
            ];
            $stockoutOrderInfo = $this->objOrmStockoutOrder->find($condition)->select(['stockout_order_id','destroy_order_status','stockout_order_status','warehouse_id'])->rows();
            if(empty($stockoutOrderInfo)) {
                Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
            }
            foreach($stockoutOrderInfo as $stockoutOrderRow) {

               // $this->objData->deleteStockoutOrder();
              $this->deleteStockoutOrder($stockoutOrderRow,'系统作废',Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID,Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME);
            }

        } catch (Exception $e) {
            $errorStr = $e->getFile() . "_" . $e->getLine() . "_" . $e->getCode() . '_' . $e->getMessage();
            Bd_Log::warning("DeleteStockoutOrder script error:" . $errorStr);
            echo $errorStr . PHP_EOL;
        }

    }

    public function deleteStockoutOrder($stockoutOrderInfo)
    {

        $updateData = [
            'stockout_order_status' => Order_Define_StockoutOrder::INVALID_STOCKOUT_ORDER_STATUS,
            'destroy_order_status' => $stockoutOrderInfo['stockout_order_status'],
        ];
        $strStockoutOrderId = $stockoutOrderInfo['stockout_order_id'];
        Model_Orm_StockoutOrder::getConnection()->transaction(function () use ($strStockoutOrderId,$updateData,$stockoutOrderInfo) {

            $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);

            if (empty($result)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL);
            }

            $operationType = Order_Define_StockoutOrder::OPERATION_TYPE_INSERT_SUCCESS;
            $userId = !empty($userId) ? $userId: Order_Define_Const::DEFAULT_SYSTEM_OPERATION_ID;
            $userName = !empty($userName) ? $userName:Order_Define_Const::DEFAULT_SYSTEM_OPERATION_NAME ;
            //释放库存(已出库不释放库存)
            if ($stockoutOrderInfo['stockout_order_status'] >= Order_Define_StockoutOrder::STOCKOUTED_STOCKOUT_ORDER_STATUS) {
                return [];
            }
            $arrStockoutDetail = $this->objOrmSku->getSkuInfoById($strStockoutOrderId, ['sku_id', 'order_amount', 'pickup_amount']);
            if (empty($arrStockoutDetail)) {
                Order_BusinessError::throwException(Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS);
            }


            $rs = $this->objData->cancelStockoutOrder($strStockoutOrderId,$stockoutOrderInfo['warehouse_id']);
        });
        Dao_Ral_Statistics::syncStatistics(Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            Order_Statistics_Type::ACTION_UPDATE,
            $strStockoutOrderId);//更新报表
    }

}

Bd_Init::init();
$objData = new DeleteStockoutOrder();
$objData->run();
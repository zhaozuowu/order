<?php

/**
 * @name Service_Data_StockoutOrder
 * @desc 出库订单操作类
 * @author zhaozuowu@iwaimai.baidu.com　
 */
class Service_Data_StockoutOrder
{
    /**
     * 出库单状态列表
     */
    const INIT_STOCKOUT_ORDER_STATUS = 10;//待审核
    const STAY_PICKING_STOCKOUT_ORDER_STATUS = 20;//待拣货
    const STAY_RECEIVED_STOCKOUT_ORDER_STATUS = 25;//待揽收
    const STOCKOUTED_STOCKOUT_ORDER_STATUS = 30;//已出库
    const RECEIVED_STOCKOUT_ORDER_STATUS = 40;//已签收
    const AUDIT_NOT_PASSED_STOCKOUT_ORDER_STATUS = 50;//审核不通过
    /**
     * 出库单状态列表
     * @var array
     */
    protected $stockoutOrderStatusList = [
        '10' => '待审核',
        '20' => '待拣货',
        '25' => '待揽收',
        '30' => '已出库',
        '40' => '已签收',
        '50' => '审核不通过',
    ];
    /**
     * orm obj
     * @var Model_Orm_StockoutOrder
     */
    protected $objOrmStockoutOrder;

    /**
     * init
     */
    public function __construct()
    {
        $this->objOrmStockoutOrder = new Model_Orm_StockoutOrder();

    }


    /**
     * 获取下一步操作的出库单操作状态
     * @param $stockoutOrderStatus 出库单号
     * @return bool
     */
    public function getNextStockoutOrderStatus($stockoutOrderStatus)
    {
        $stockoutOrderList = $this->stockoutOrderStatusList;
        if (!array_key_exists($stockoutOrderStatus, $stockoutOrderList)) {
            return false;
        }
        $keys = array_keys($stockoutOrderList);
        $result = $keys[array_search($stockoutOrderStatus, $keys) + 1] ?? false;
        return $result;

    }

    /**
     * 根据出库单号，更新出库单状态完成揽收
     * @param $strStockoutOrderId 出库单号
     * @return array
     * @throws Order_BusinessError
     */
    public function deliveryOrder($strStockoutOrderId)
    {

        if (empty($strStockoutOrderId)) {
            Bd_Log::warning(__METHOD__ . ' called, input params: ' . json_encode(func_get_args()));
            Order_BusinessError::throwException(Order_Error_Code::PARAMS_ERROR);
        }
        $stockoutOrderInfo = $this->objOrmStockoutOrder->getStockoutOrderInfoById($strStockoutOrderId);//获取出库订单信息

        if (empty($stockoutOrderInfo)) {
            Bd_Log::warning(__METHOD__ . ' get stockoutOrderInfo by stockout_order_id:' . $strStockoutOrderId . 'no data');
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS);
        }

        $stayRecevied = Service_Data_StockoutOrder::STAY_RECEIVED_STOCKOUT_ORDER_STATUS;//获取待揽收状态
        if ($stockoutOrderInfo['stockout_order_status'] != $stayRecevied) {
            Bd_Log::warning(__METHOD__ . ' no allow update stockout_order_status become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE);

        }

        $nextStockoutOrderStatus = $this->getNextStockoutOrderStatus($stockoutOrderInfo['stockout_order_status']);//获取下一步操作状态
        if (empty($nextStockoutOrderStatus)) {
            Bd_Log::warning(__METHOD__ . ' update stockout_order_status fail  become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        $updateData = ['stockout_order_status' => $nextStockoutOrderStatus];
        $result = $this->objOrmStockoutOrder->updateStockoutOrderStatusById($strStockoutOrderId, $updateData);
        if (empty($result)) {
            Bd_Log::warning(__METHOD__ . ' update stockout_order_status fail  become stockoutinfo:' . json_encode($stockoutOrderInfo));
            Order_BusinessError::throwException(Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL);
        }
        return [];
    }


    /**
     * 获取业态订单列表
     * @param $arrInput
     * @return array
     */
    public function getBusinessFormOrderList($arrInput)
    {

        $arrConditions = $this->getListConditions($arrInput);
        if (false === $arrConditions) {
            return [];
        }
        $intLimit = intval($arrInput['page_size']);
        $intOffset = (intval($arrInput['page_num']) - 1) * $intLimit;
        $arrQuotationList = Model_Orm_Quotation::getQuotationListByConditions($arrConditions, [], $intOffset, $intLimit);
        $objDsVendor = new Service_Data_Vendor();
        $arrQuotationList = $objDsVendor->appendVendorNameToList($arrQuotationList);
        return $arrQuotationList;

    }

    /**
     * 获取查询的条件
     *
     * @param array $arrInput
     * @return array
     */
    public function getListConditions($arrInput)
    {
        $arrConditions = [];
        $arrConditions['is_delete'] = Order_Define_Const::NOT_DELETE;
        if (!empty($arrInput['status'])) {
            $arrConditions['quotation_status'] = $arrInput['quotation_status'];
        }
        if (!empty($arrInput['quotation_id'])) {
            $arrConditions['quotation_id'] = $arrInput['quotation_id'];
        }
        if (!empty($arrInput['vendor_name'])) {
            $arrVendorIds = Model_Orm_Vendor::getVendorIdsByVendorName($arrInput['vendor_name']);
            if (empty($arrVendorIds)) {
                return false;
            }
            $arrConditions['vendor_id'] = ['in', $arrVendorIds];
        }
        if (!empty($arrInput['quotation_creator'])) {
            $arrConditions['quotation_create_uname'] = $arrInput['quotation_creator'];
        }
        if (!empty($arrInput['start_time'])) {
            $arrConditions['quotation_end_time'] = ['>=', $arrInput['start_time']];
        }
        if (!empty($arrInput['end_time'])) {
            $arrConditions['quotation_start_time'] = ['<=', $arrInput['end_time']];
        }
        return $arrConditions;
    }
}
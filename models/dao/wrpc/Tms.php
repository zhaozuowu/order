<?php
/**
 * @name Dao_Wrpc_Tms
 * @desc interact with tms
 * @author jinyu02@iwaimai.baidu.com
 */
class Dao_Wrpc_Tms
{
    /**
     * wrcp service
     * @var Bd_Wrpc_Client
     */
    private $objWrpcService;

    /**
     * init
     */
    public function __construct()
    {
        $this->objWrpcService = new Bd_Wrpc_Client(Order_Define_Wrpc::TMS_APP_ID,
                                                    Order_Define_Wrpc::TMS_NAMESPACE,
                                                    Order_Define_Wrpc::TMS_SERVICE_NAME);
    }

    /**
     * 创建tms运单
     * @param array $arrInput
     * @return integer
     * @throws Order_BusinessError
     */
    public function createShipmentOrder($arrInput) {
        $strRoutingKey = sprintf("loc=%s", $arrInput['warehouse_location']);
        $this->objWrpcService->setMeta(["routing-key"=>$strRoutingKey]);
        $arrParams = $this->getCreateShipmentParams($arrInput);
        $arrRet = $this->objWrpcService->processWarehouseRequest($arrParams);
        Bd_Log::trace(sprintf("method[%s] processWarehouseRequest[%s]",
                                __METHOD__, json_encode($arrRet)));
        if (empty($arrRet['data']) || 0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] routing-key[%s]",
                                        __METHOD__, json_encode($arrRet), $strRoutingKey));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_CREATE_SHIPMENTORDER_ERROR);
        }
        return $arrRet['data'];
    }

    /**
     * 通知tms拣货数量
     * @param string $strCustomerLocation
     * @param integer $intShipmentOrderId
     * @param array $arrPickupSkus
     * @return void
     * @throws Order_BusinessError
     */
    public function notifyPickupAmount($intShipmentOrderId, $arrPickupSkus) {
        $strRoutingKey = sprintf("shipmentid=%s", $intShipmentOrderId);
        $this->objWrpcService->setMeta(["routing-key"=>$strRoutingKey]);
        $arrParams = $this->getPickingAmountParams($intShipmentOrderId, $arrPickupSkus);
        $arrRet = $this->objWrpcService->pickingAmount($arrParams);
        Bd_Log::trace(sprintf("method[%s] pickingAmount[%s]", __METHOD__, json_encode($arrRet)));
        if (0 != $arrRet['errno']) {
            Bd_Log::warning(sprintf("method[%s] arrRet[%s] routing-key[%s]",
                                    __METHOD__, json_encode($arrRet), $strRoutingKey));
            Order_BusinessError::throwException(Order_Error_Code::NWMS_ORDER_STOCKOUT_NOTIFY_FINISHPICKUP_ERROR);
        }
    }

    /**
     * 拼接通知拣货数量参数
     * @param integer $intShipmentOrderId
     * @param array $arrPickupSkus
     * @return array
     */
    protected function getPickingAmountParams($intShipmentOrderId, $arrPickupSkus) {
        $arrParams = [];
        $arrReceiptProductsInfo = [];
        $arrReceiptProductsInfo['shipmentId'] = $intShipmentOrderId;
        $arrReceiptProductsInfo['receiptProducts'] = $this->getReceiptProducts($arrPickupSkus);
        $arrParams['receiptProductsInfo'] = $arrReceiptProductsInfo;
        return $arrParams;
    }

    /**
     * 拼接拣货完成商品参数
     * @param array $arrSkus
     * @return array
     */
    protected function getReceiptProducts($arrSkus) {
        $arrReceiptProducts = [];
        if (empty($arrSkus)) {
            return $arrReceiptProducts;
        }
        foreach ((array)$arrSkus as $arrSkuItem) {
            $arrReceiptProductItem = [];
            $arrReceiptProductItem['skuId'] = empty($arrSkuItem['sku_id']) ? 0 : $arrSkuItem['sku_id'];
            $arrReceiptProductItem['receiptAmount'] = empty($arrSkuItem['pickup_amount']) ?
                                                    0 : $arrSkuItem['pickup_amount'];
            $arrReceiptProducts[] = $arrReceiptProductItem;
        }
        return (array)$arrReceiptProducts;
    }

    /**
     * 获取运单创建参数
     * @param array $arrInput
     * @return array
     */
    protected function getCreateShipmentParams($arrInput) {
        $arrParams = [];
        $arrParams['user'] = (object)[];
        $arrParams['warehouseRequest'] = $this->getWarehouseRequest($arrInput);
        return $arrParams;
    }

    /**
     * 拼接创建运单参数
     * @param array $arrInput
     * @return array
     */
    protected function getWarehouseRequest($arrInput) {
        $arrWarehouseRequest = [];
        $arrShelfInfo = $arrInput['shelf_info'];
        $arrShelfInfo['devices'] = (object)$arrShelfInfo['devices'];
        $arrExpectArriveTime = $arrInput['expect_arrive_time'];
        $arrWarehouseRequest['warehouseId'] = empty($arrInput['warehouse_id']) ? '' : intval($arrInput['warehouse_id']);
        $arrWarehouseRequest['businessType'] = empty($arrInput['business_form_order_type']) ? 0 : strval($arrInput['business_form_order_type']);
        $arrWarehouseRequest['businessSubType'] = empty($arrShelfInfo['supply_type']) ? 0 : $arrShelfInfo['supply_type'];
        $arrWarehouseRequest['businessJson'] = json_encode($arrShelfInfo);
        $arrWarehouseRequest['orderRemark'] = empty($arrInput['business_form_order_remark']) ? '' : strval($arrInput['business_form_order_remark']);
        $arrWarehouseRequest['stockoutNumber'] = empty($arrInput['stockout_order_id']) ? 0 : intval($arrInput['stockout_order_id']);
        $arrWarehouseRequest['orderNumber'] = empty($arrInput['logistics_order_id']) ? 0 : intval($arrInput['logistics_order_id']);
        $arrWarehouseRequest['requireReceiveStartTime'] = empty($arrExpectArriveTime['start']) ? 0 : $arrExpectArriveTime['start'];
        $arrWarehouseRequest['requireReceiveEndTime'] = empty($arrExpectArriveTime['end']) ? 0 : $arrExpectArriveTime['end'];
        $arrWarehouseRequest['products'] = $this->getProducts($arrInput['skus']);
        $arrWarehouseRequest['userInfo'] = $this->getUserInfo($arrInput);
        return $arrWarehouseRequest;
    }

    /**
     * 拼接商品信息参数
     * @param array $arrSkus
     * @return array
     */
    protected function getProducts($arrSkus) {
        $arrProduts = [];
        if (empty($arrSkus)) {
            return $arrProduts;
        }
        foreach ((array)$arrSkus as $arrSkuItem) {
            $arrProdutItem = [];
            $arrProdutItem['skuId'] = empty($arrSkuItem['sku_id']) ? 0 : $arrSkuItem['sku_id'];
            $arrProdutItem['name'] = empty($arrSkuItem['sku_name']) ? '' : $arrSkuItem['sku_name'];
            $arrProdutItem['amount'] = empty($arrSkuItem['distribute_amount']) ? 0 : $arrSkuItem['distribute_amount'];
            $arrProdutItem['netWeight'] = empty($arrSkuItem['sku_net']) ? '' : intval($arrSkuItem['sku_net']);
            $arrProdutItem['netWeightUnit'] = empty($arrSkuItem['sku_net_unit']) ? 0 : intval($arrSkuItem['sku_net_unit']);
            $arrProdutItem['upcUnit'] = empty($arrSkuItem['upc_unit']) ? 0 : intval($arrSkuItem['upc_unit']);
            $arrProdutItem['specifications'] = empty($arrSkuItem['upc_unit_num']) ? 0 : intval($arrSkuItem['upc_unit_num']);
            $arrProduts[] = $arrProdutItem;
        }
        return $arrProduts;
    }

    /**
     * 拼接客户信息参数
     * @param $arrInput
     * @return array
     */
    protected function getUserInfo($arrInput) {
        $arrUserInfo = [];
        if (empty($arrInput)) {
            return [];
        }
        $arrUserInfo['npName'] = empty($arrInput['customer_name']) ? '' : strval($arrInput['customer_name']);
        $arrUserInfo['npId'] = empty($arrInput['customer_id']) ? 0 : strval($arrInput['customer_id']);
        $arrUserInfo['contactName'] = empty($arrInput['customer_contactor']) ? '' : strval($arrInput['customer_contactor']);
        $arrUserInfo['contactPhone'] = empty($arrInput['customer_contact']) ? '' : strval($arrInput['customer_contact']);
        $arrUserInfo['customerServiceName'] = empty($arrInput['executor']) ? '' : strval($arrInput['executor']);
        $arrUserInfo['customerServicePhone'] = empty($arrInput['executor_contact']) ? '' : strval($arrInput['executor_contact']);
        $arrUserInfo['poi'] = (object)$this->getPoi($arrInput);
        return $arrUserInfo;
    }

    /**
     * 拼接客户坐标信息
     * @param $arrInput
     * @return array
     */
    protected function getPoi($arrInput) {
        $arrPoiInfo = [];
        if (empty($arrInput)) {
            return [];
        }
        $arrLocation = explode(',', $arrInput['customer_location']);
        $arrPoiInfo['longitude'] = empty($arrLocation[0]) ? 0 : floatval($arrLocation[0]);
        $arrPoiInfo['latitude'] = empty($arrLocation[1]) ? 0 : floatval($arrLocation[1]);
        $arrPoiInfo['address'] = empty($arrInput['customer_address']) ? '' : strval($arrInput['customer_address']);
        $arrPoiInfo['areaCode'] = empty($arrInput['customer_region_id']) ? '' : strval($arrInput['customer_region_id']);
        $arrPoiInfo['cityId'] = empty($arrInput['customer_city_id']) ? 0 : intval($arrInput['customer_city_id']);
        $arrPoiInfo['cityName'] = empty($arrInput['customer_city_name']) ? '' : strval($arrInput['customer_city_name']);
        $arrPoiInfo['districtId'] = empty($arrInput['customer_region_id']) ? 0 : intval($arrInput['customer_region_id']);
        $arrPoiInfo['districtName'] = empty($arrInput['customer_region_name']) ? '' : strval($arrInput['customer_region_name']);
        $arrPoiInfo['coordsType'] = empty($arrInput['customer_location_source']) ? 0 : intval($arrInput['customer_location_source']);
        return $arrPoiInfo;
    }
}
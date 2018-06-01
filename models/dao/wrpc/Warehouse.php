<?php
/**
 * @name Warehouse.php
 * @desc Warehouse.php
 * @author yu.jin03@ele.me
 */

class Dao_Wrpc_Warehouse
{
    /**
     * @var Bd_Wrpc_Client
     */
    private $objWrpcService;

    /**
     * Dao_Wrpc_Warehouse constructor.
     */
    public function __construct($strServiceName = Order_Define_Wrpc::NWMS_WAREHOUSE_SERVICE_NAME)
    {
        $this->objWrpcService = new Bd_Wrpc_Client(Order_Define_Wrpc::NWMS_APP_ID,
            Order_Define_Wrpc::NWMS_WAREHOUSE_NAMESPACE, $strServiceName
            );
    }

    /**
     * 获取仓库信息
     * @param $intWarehouseId
     * @return mixed
     */
    public function getWarehouseInfoByWarehouseId($intWarehouseId)
    {
        $arrParams['warehouse_id'] = $intWarehouseId;
        $arrRet = $this->objWrpcService->getWarehouseByConds($arrParams);
        return $arrRet['data']['query_result'][0];
    }

    /**
     * sug库位信息
     * @param $intWarehouseId
     * @param $strLocationCode
     * @param $intIsDefault
     * @return array
     */
    public function sugStorageLocation($intWarehouseId, $strLocationCode, $intIsDefaultStore)
    {
        $arrParams = [
            'objData' => [
                'warehouse_id'  => $intWarehouseId,
                'location_code' => $strLocationCode,
                'is_default_store'    => $intIsDefaultStore,
            ]
        ];
        $arrRet = $this->objWrpcService->sugStorageLocation($arrParams);
        return (array)$arrRet['data'];
    }
}
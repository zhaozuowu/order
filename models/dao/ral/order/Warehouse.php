<?php
/**
 * @name Dao_Ral_Order_Warehouse
 * @desc Dao_Ral_Order_Warehouse
 * @author zhaozuowu@iwaimai.baidu.com
 * Class Dao_Ral_Order_Warehouse
 */
class Dao_Ral_Order_Warehouse
{
    /**
     * api raler
     * @var Order_ApiRaler
     */
    protected $objApiRal;

    /**
     * get sku list
     * @var string
     */
    const API_RALER_GET_Warehouse_LIST = 'getwarehouselist';

    /**
     * get warehouse info by city id
     * @var string
     */
    const API_RALER_GET_WAREHOUSE_BY_CITY = 'getwarehousebycityid';

    /**
     * get warehouse info by id
     * @var string
     */
    const API_RALER_GET_WAREHOUSE_INFO_BY_ID = 'getwarehousebyid';

    /**
     * init
     */
    public function __construct()
    {
        $this->objApiRal = new Order_ApiRaler();
    }

    /**
     * 根据仓库id，获取仓库信息
     * @return mixed
     * @throws Nscm_Exception_Error
     */
    public function getWareHouseList($arrWarehouseIds)
    {

        $strWarehouseId = is_array($arrWarehouseIds) ? implode(',', $arrWarehouseIds) : $arrWarehouseIds;
        $req[self::API_RALER_GET_Warehouse_LIST] = [
            'warehouse_id' => $strWarehouseId,
        ];
        $ret = $this->objApiRal->getData($req);
        $ret = !empty($ret[self::API_RALER_GET_Warehouse_LIST]) ? $ret[self::API_RALER_GET_Warehouse_LIST] : [];
        return $ret;

    }

    /**
     * get warehouse info by city id
     * @param integer $intCityId
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getWareHouseInfoByCityId($intCityId) {
        $ret = [];
        if (empty($intCityId)) {
            return $ret;
        }
        $req[self::API_RALER_GET_WAREHOUSE_BY_CITY]['city_id'] = $intCityId;
        $ret = $this->objApiRal->getData($req);
        $ret = !empty($ret[self::API_RALER_GET_WAREHOUSE_BY_CITY]) ? $ret[self::API_RALER_GET_WAREHOUSE_BY_CITY] : [];
        return $ret;        
    }

    /**
     * get warehouse info by conds
     * @param $intDistrictId
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getWarehouseInfoByDistrictId($intDistrictId) {
        $ret = [];
        if (empty($intDistrictId)) {
            return $ret;
        }
        $req[self::API_RALER_GET_Warehouse_LIST]['district_id'] = $intDistrictId;
        $ret = $this->objApiRal->getData($req);
        $ret = !empty($ret[self::API_RALER_GET_Warehouse_LIST]['query_result']) ?
                $ret[self::API_RALER_GET_Warehouse_LIST]['query_result'] : [];
        return $ret;
    }

    /**
     * get warehouse info by warehouse id
     * @param int $intWarehouseId
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getWarehouseInfoByWarehouseId($intWarehouseId)
    {
        $req = [
            self::API_RALER_GET_WAREHOUSE_INFO_BY_ID => [
                'warehouse_id' => strval($intWarehouseId),
            ],
        ];
        Bd_Log::debug('ral get warehouse info request params: ' . json_encode($req));
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug('ral get warehouse info response params: ' . json_encode($ret));
        return $ret[self::API_RALER_GET_WAREHOUSE_INFO_BY_ID]['query_result'][0] ?? [];
    }

    /**
     * get warehouse info by warehouse ids
     * @param array $arrWarehouseIds
     * @return array
     * @throws Nscm_Exception_Error
     */
    public function getWarehouseInfoMapByWarehouseIds($arrWarehouseIds)
    {
        $req = [
            self::API_RALER_GET_WAREHOUSE_INFO_BY_ID => [
                'warehouse_id' => implode(',', $arrWarehouseIds),
            ],
        ];
        Bd_Log::debug('ral get warehouse info request params: ' . json_encode($req));
        $ret = $this->objApiRal->getData($req);
        Bd_Log::debug('ral get warehouse info response params: ' . json_encode($ret));
        $arrWarehouseInfo = $ret[self::API_RALER_GET_WAREHOUSE_INFO_BY_ID]['query_result'] ?? [];
        $arrWarehouseInfoMap = [];
        foreach ($arrWarehouseInfo as $arrItem) {
            $arrWarehouseInfoMap[$arrItem['warehouse_id']] = $arrItem;
        }
        Bd_Log::debug('warehouse info map: ' . json_encode($arrWarehouseInfoMap));
        return $arrWarehouseInfoMap;
    }

}

?>
<?php
/**
 * @name Service_Data_Warehouse
 * @desc 仓库相关
 * @author hang.song02@ele.me
 */

class Service_Data_Warehouse
{
    /**
     * 获取仓库是否开启库区库位
     * @param int $intWarehouseId
     * @return bool
     */
    public function getWarehouseLocationTag($intWarehouseId)
    {
        $daoWrpcWarehouse = new Dao_Wrpc_Warehouse();
        $arrWarehouseInfo = $daoWrpcWarehouse->getWarehouseInfoByWarehouseId($intWarehouseId);
        return $arrWarehouseInfo['storage_location_tag'];
    }
}
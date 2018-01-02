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
     * 根据仓库id，获取仓库信息
     * @return mixed
     */
    public function getWareHouseList($arrWarehouseIds)
    {
        $strWarehouseId = is_array($arrWarehouseIds) ? implode(',', $arrWarehouseIds) : $arrWarehouseIds;
        $header = array(
            "pathinfo" => "/warehouse/api/getwarehousebyid?warehouse_id=" . $strWarehouseId,
        );
        $ret = ral('order', "get", $input, rand(), $header);
        if ($ret === false) {
            Bd_Log::warning(__METHOD__ . 'ral error service_' . ral_get_error());
            return $ret;
        }
        $result = json_decode($ret, true);
        if ($result['code'] != 0) {
            Bd_Log::warning(__METHOD__ . 'ral result service_' . $ret);
            return [];
        }
        return $result['result'];

    }


}

?>
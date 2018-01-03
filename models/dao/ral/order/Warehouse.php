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
     * init
     */
    public function __construct()
    {
        $this->objApiRal = new Order_ApiRaler();
    }

    /**
     * 根据仓库id，获取仓库信息
     * @return mixed
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


}

?>
<?php
/**
 * @name Warehouse.php
 * @desc Warehouse.php
 * @author yu.jin03@ele.me
 */

class Order_Define_Warehouse
{
    /**
     * 仓库开启库区库位
     * @var integer
     */
    const STORAGE_LOCATION_TAG_ENABLED = 1;

    /**
     * 仓库停用库区库位
     * @var integer
     */
    const STORAGE_LOCATION_TAG_DISABLE = 2;

    /**
     * 仓库是否开启库区库位枚举
     * @var array
     */
    const STORAGE_LOCATION_TAG_MAP = [
        self::STORAGE_LOCATION_TAG_ENABLED => '启用',
        self::STORAGE_LOCATION_TAG_DISABLE => '停用',
    ];
}
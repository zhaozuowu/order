<?php
/**
 * Order_Exception_Const
 * User: bochao.lv
 * Date: 2018/3/9
 * Time: 14:38
 */

class Order_Exception_Const
{

    /**
     * level no use
     * @var int
     */
    const LEVEL_NO_USE = 0;

    /**
     * type sku
     * @var int
     */
    const TYPE_SKU = 1;

    /**
     * type vendor
     * @var int
     */
    const TYPE_VENDOR = 2;

    /**
     * type warehouse
     * @var int
     */
    const TYPE_WAREHOUSE = 3;

    /**
     * type stock
     * @var int
     */
    const TYPE_STOCK = 4;

    /**
     * concrete sku no quotation
     * @var int
     */
    const CONCRETE_SKU_NO_QUOTATION = 101;

    /**
     * concrete sku not open
     * @var int
     */
    const CONCRETE_SKU_NOT_OPEN = 102;

    /**
     * concrete sku business fail
     * @var int
     */
    const CONCRETE_SKU_BUSINESS_FAIL = 103;

    /**
     * concrete sku not exist
     * @var int
     */
    const CONCRETE_SKU_NOT_EXIST = 104;

    /**
     * concrete sku no vendor
     * @var int
     */
    const CONCRETE_SKU_NO_VENDOR = 201;

    /**
     * concrete warehouse
     * @var int
     */
    const CONCRETE_WAREHOUSE = 301;

    /**
     * concrete stock not enough
     * @var int
     */
    const CONCRETE_STOCK_NOT_ENOUGH = 401;

    /**
     * type map
     * @var array
     */
    const MAP_TYPE = [
        self::CONCRETE_SKU_NO_QUOTATION => self::TYPE_SKU,
        self::CONCRETE_SKU_NOT_OPEN => self::TYPE_SKU,
        self::CONCRETE_SKU_BUSINESS_FAIL => self::TYPE_SKU,
        self::CONCRETE_SKU_NOT_EXIST => self::TYPE_SKU,
        self::CONCRETE_SKU_NO_VENDOR => self::TYPE_VENDOR,
        self::CONCRETE_WAREHOUSE => self::TYPE_WAREHOUSE,
        self::CONCRETE_STOCK_NOT_ENOUGH => self::TYPE_STOCK,
    ];

    /**
     * test map
     * @var array
     */
    const MAP_TEXT = [
        self::CONCRETE_SKU_NO_QUOTATION => '【商品信息】商品无生效报价',
        self::CONCRETE_SKU_NOT_OPEN => '【商品信息】商品库商品未启用',
        self::CONCRETE_SKU_BUSINESS_FAIL => '【商品信息】商品订货状态不可用',
        self::CONCRETE_SKU_NOT_EXIST => '【商品信息】商品不存在',
        self::CONCRETE_SKU_NO_VENDOR => '【供货商信息异常】商品无供货商',
        self::CONCRETE_WAREHOUSE => '【仓库信息异常】网点没有所对应的仓库',
        self::CONCRETE_STOCK_NOT_ENOUGH => '【库存信息异常】没有可用库存',
    ];
}
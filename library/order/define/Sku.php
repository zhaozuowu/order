<?php
/**
 * @name Order_Define_Sku
 * @desc Order_Define_Sku
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Define_Sku
{
    /**
     * from china
     * @var int
     */
    const SKU_FROM_COUNTRY_CHINA = 1;

    /**
     * not from china
     * @var int
     */
    const SKU_FROM_COUNTRY_FOREIGN = 2;

    /**
     * from country map
     * @var array
     */
    const SKU_FROM_COUNTRY_MAP = [
        self::SKU_FROM_COUNTRY_CHINA => '国产',
        self::SKU_FROM_COUNTRY_FOREIGN => '进口',
    ];

    /**
     * net gram
     * @var int
     */
    const SKU_NET_GRAM = 2;

    /**
     * net kg
     * @var int
     */
    const SKU_NET_KILOGRAM = 1;

    /**
     * net ml
     * @var int
     */
    const SKU_NET_MILLILITER = 3;

    /**
     * net l
     * @var int
     */
    const SKU_NET_LITER = 4;

    /**
     * net map
     * @var array
     */
    const SKU_NET_MAP = [
        self::SKU_NET_GRAM => '克',
        self::SKU_NET_KILOGRAM => '千克',
        self::SKU_NET_MILLILITER => '毫升',
        self::SKU_NET_LITER => '升',
    ];


    /**
     * upc单位
     * @var array
     */
    const UPC_UNIT_MAP = [
        1 => "箱",
        2 => "袋",
        3 => "包",
        4 => "瓶",
        5 => "盒",
        6 => "罐",
        7 => "条",
        8 => "件",
        9 => "个",
        10 => "桶",
        11 => "杯",
        12 => "根",
    ];

    /**
     * 生产日期类型
     */
    const SKU_EFFECT_TYPE_PRODUCT = 1;

    /**
     * 失效日期类型
     */
    const SKU_EFFECT_TYPE_EXPIRE = 2;

    /**
     * expire effect type define
     * @var array
     */
    const SKU_EFFECT_TYPE_EXPIRE_MAP = [
        self::SKU_EFFECT_TYPE_PRODUCT => '生产日期',
        self::SKU_EFFECT_TYPE_EXPIRE => '失效日期',
    ];

    /**
     * sku price type benefit
     * @var integer
     */
    const SKU_PRICE_TYPE_BENEFIT = 1;

    /**
     * sku price type stable
     * @var integer
     */
    const SKU_PRICE_TYPE_STABLE = 2;

    /**
     * sku price type cost
     * @var sku price type cost
     */
    const SKU_PRICE_TYPE_COST = 3;

    /**
     * sku tax rate
     * @var array
     */
    const SKU_TAX_RATE = [
        1 => '17%',
        2 => '11%',
        3 => '6%',
        4 => '0',
    ];

    /**
     * sku tax num
     * @var array
     */
    const SKU_TAX_NUM = [
        1 => 17,
        2 => 11,
        3 => 6,
        4 => 0,
    ];
}
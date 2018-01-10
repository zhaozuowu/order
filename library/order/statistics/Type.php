<?php
/**
 * @name Order_Statistics_Type
 * @desc 统计类型
 * @author lvbochao@iwaimai.baidu.com
 */

class Order_Statistics_Type
{
    /**
     * action create
     * @var int
     */
    const ACTION_CREATE = 1;

    /**
     * action update
     * @var int
     */
    const ACTION_UPDATE = 2;

    /**
     * table stockin reserve
     * @var int
     */
    const TABLE_STOCKIN_RESERVE = 1;

    /**
     * table stockin stockout
     * @var int
     */
    const TABLE_STOCKIN_STOCKOUT = 2;

    /**
     * table stockout order
     * @var int
     */
    const TABLE_STOCKOUT_ORDER = 3;

    /**
     *orm
     * @var int
     */
    const ORM = 1;

    /**
     * nscm
     * @var int
     */
    const NSCM = 2;

    /**
     * master table
     * @var int
     */
    const MASTER_TABLE = 1;

    /**
     * slave table
     * @var int
     */
    const SLAVE_TABLE = 2;

    /**
     * function
     * @var int
     */
    const FUNCTION = 1;

    /**
     * array
     * @var int
     */
    const ARRAY = 2;

    /**
     * function array
     * @var int
     */
    const FUNCTION_ARRAY = 3;

    /**
     * multiply
     * @var int
     */
    const MULTIPLY = 4;

    /**
     * json
     * @var int
     */
    const JSON = 5;

    /**
     * repeat
     * @var int
     */
    const REPEAT = 1;

    /**
     * single
     * @var int
     */
    const SINGLE = 2;

    /**
     * table map
     * @var array
     */
    const TABLE_MAP = [
        self::TABLE_STOCKIN_RESERVE => 'STOCKIN_RESERVE',
        self::TABLE_STOCKIN_STOCKOUT => 'STOCKIN_STOCKOUT',
        self::TABLE_STOCKOUT_ORDER => 'STOCKOUT_ORDER',
    ];

    /**
     * operate map
     * @var array
     */
    const OPERATE_MAP = [
        self::ACTION_CREATE => true,
        self::ACTION_UPDATE => true,
    ];

    /**
     * stockin map
     * @var array
     */
    const STOCKIN_MAP = [
        Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE => self::TABLE_STOCKIN_RESERVE,
        Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT => self::TABLE_STOCKIN_STOCKOUT,
    ];
}
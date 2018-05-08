#!php/bin/php
<?php
/**
 * @name AppendStatisticsOrder
 * @desc app statistics order
 * @author lvbochao@iwaimai.baidu.com
 */

Bd_Init::init();

try {
    Bd_Log::trace('script start run, argvs: ' . json_encode($argv));
    $objAso = new AppendStatisticsOrderByTime();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendStatisticsOrderByTime
{
    /**
     * @var Service_Data_Statistics_Statistics
     */
    private $objData;

    /**
     * AppendStatisticsOrder constructor.
     */
    function __construct()
    {
        $this->objData = new Service_Data_Statistics_Statistics();
    }

    const LIMIT = 20;

    /**
     * work
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function work()
    {
        $arrAllConf = [
            [
                'condition' => [
                    'create_time' => [
                        'between' => [
                            $this->intStart,
                            $this->intEnd,
                        ],
                    ],
                    'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE,
                    'stockin_order_status' => Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH,
                ],
                'source_table' => 'Model_Orm_StockinOrder',
                'source_column' => 'stockin_order_id',
                'destination_table' => 'Model_Orm_StockinReserveDetail',
                'action' => Order_Statistics_Type::ACTION_CREATE,
                'type' => Order_Statistics_Type::TABLE_STOCKIN_RESERVE,
            ],
            [
                'condition' => [
                    'create_time' => [
                        'between' => [
                            $this->intStart,
                            $this->intEnd,
                        ],
                    ],
                    'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT,
                    'stockin_order_status' => Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH,
                    'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_MANUAL_CREATE,
                ],
                'source_table' => 'Model_Orm_StockinOrder',
                'source_column' => 'stockin_order_id',
                'destination_table' => 'Model_Orm_StockinStockoutDetail',
                'action' => Order_Statistics_Type::ACTION_CREATE,
                'type' => Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT,
            ],
            [
                'condition' => [
                    'update_time' => [
                        'between' => [
                            $this->intStart,
                            $this->intEnd,
                        ],
                    ],
                    'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT,
                    'stockin_order_status' => Order_Define_StockinOrder::STOCKIN_ORDER_STATUS_FINISH,
                    'data_source' => Order_Define_StockinOrder::STOCKIN_DATA_SOURCE_FROM_SYSTEM,
                ],
                'source_table' => 'Model_Orm_StockinOrder',
                'source_column' => 'stockin_order_id',
                'destination_table' => 'Model_Orm_StockinStockoutDetail',
                'action' => Order_Statistics_Type::ACTION_CREATE,
                'type' => Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT,
            ],
            [
                'condition' => [
                    'create_time' => [
                        'between' => [
                            $this->intStart,
                            $this->intEnd,
                        ],
                    ],
                ],
                'source_table' => 'Model_Orm_StockoutOrder',
                'source_column' => 'stockout_order_id',
                'destination_table' => 'Model_Orm_StockoutOrderDetail',
                'action' => Order_Statistics_Type::ACTION_CREATE,
                'type' => Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            ],
            [
                'condition' => [
                    'update_time' => [
                        'between' => [
                            $this->intStart,
                            $this->intEnd,
                        ],
                    ],
                    'create_time' => ['<', $this->intStart],
                ],
                'source_table' => 'Model_Orm_StockoutOrder',
                'source_column' => 'stockout_order_id',
                'destination_table' => 'Model_Orm_StockoutOrderDetail',
                'action' => Order_Statistics_Type::ACTION_UPDATE,
                'type' => Order_Statistics_Type::TABLE_STOCKOUT_ORDER,
            ],
        ];
        foreach ($arrAllConf as $arrConf) {
            // reserve stockin
            $arrCondition = $arrConf['condition'];
            // create time
            // get all order id
            /**
             * @var Order_Base_Orm $strSourceTable
             */
            $strSourceTable = $arrConf['source_table'];
            $strSourceColumn = $arrConf['source_column'];
            /**
             * @var Order_Base_Orm $strDestination
             */
            $strDestination = $arrConf['destination_table'];
            $intAction = $arrConf['action'];
            $intType = $arrConf['type'];
            $arrAllOrderId = $strSourceTable::find($arrCondition)->distinct()->select([$strSourceColumn])->column();
            $intOffset = 0;
            $intLimit = self::LIMIT;
            $intSleepLimit = 0;
            do {
                $arrOrderId = array_slice($arrAllOrderId, $intOffset, $intLimit);
                $intCount = count($arrOrderId);
                if (empty($intCount)) {
                    break;
                }
                $intOffset += $intCount;
                $arrStatisticsCondition = [
                    'stockin_order_id' => ['in', $arrOrderId],
                ];
                $arrAllStatisticsOrderId = $strDestination::find($arrStatisticsCondition)
                    ->distinct()->select([$strSourceColumn])->column();
                $arrIntersect = array_diff($arrOrderId, $arrAllStatisticsOrderId);
                foreach ($arrIntersect as $intOrderId) {
                    $this->append($intAction, $intType, $intOrderId);
                }
                $intSleepLimit += $arrIntersect;
                if ($intSleepLimit > self::LIMIT) {
                    sleep(1);
                    $intSleepLimit -= self::LIMIT;
                }
            } while (!empty($intCount));
        }
    }


    /**
     * work
     * @param int $intType <p>
     * type: 类型，1-新增，2-修改
     * </p>
     * @param int $intTable<p>
     * table：表，1-采购入库，2-销退入库，3-出库
     * </p>
     * @param int $intKey<p>
     * key：主键，即单号，不包含字母
     * </p>
     * @throws Order_BusinessError
     * @throws Order_Error
     * @throws Nscm_Exception_Error
     */
    public function append($intType, $intTable, $intKey)
    {
        Bd_Log::trace(__METHOD__ . ' request params: ' . json_encode(func_get_args()));
        switch (intval($intType)) {
            case Order_Statistics_Type::ACTION_CREATE:
                $this->objData->addOrderStatistics($intKey, $intTable);
                break;
            case Order_Statistics_Type::ACTION_UPDATE:
                $this->objData->updateOrderStatistics($intKey, $intTable);
                break;
            default:
                Bd_Log::warning('type error! input type: ' . $intType);
        }
    }

    /**
     * show tips
     */
    public function showTips() {
        $strTips = <<<EOF
php/bin/php AppendStatisticsOrderByTime.php
--start=START_TIME [--end=END_TIME]
example
php/bin/php AppendStatisticsOrderByTime.php --start=20180101000000 --end=20180102000000
sync statistics create_time and update time between 2018-01-01 00:00:00 to 2018-01-02 00:00:00
EOF;
        echo $strTips;
    }

    /**
     * type
     * @var int
     */
    private $intStart;

    /**
     * table
     * @var int
     */
    private $intEnd;

    /**
     * opts
     * @var array
     */
    const OPTS =[
        'start::',
        'end::',
    ];

    public function getParams() {
        $arrOption = getopt('', self::OPTS);
        Bd_Log::trace('user input params: ' . json_encode($arrOption));
        $strStart = $arrOption['start'];
        $intStart = strtotime($strStart);
        $strEnd = $arrOption['end'];
        if (empty($strEnd)) {
            $intEnd = time();
        } else {
            $intEnd = strtotime($strEnd);
        }
        $this->intStart = $intStart;
        $this->intEnd = $intEnd;
    }
}
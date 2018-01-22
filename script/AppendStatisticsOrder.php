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
    $objAso = new AppendStatisticsOrder();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendStatisticsOrder
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

    /**
     * work
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     * @throws Order_Error
     */
    public function work()
    {
        $this->getParams();
        if (empty($this->intType) || empty($this->intTable) || empty($this->intKey)) {
            $this->showTips();
            exit;
        }
        $this->append($this->intType, $this->intTable, $this->intKey);
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
php/bin/php AppendStatisticsOrder.php
--type=TYPE --table=TABLE --key=KEY
TYPE:
    1 create
    2 update
TABLE: 
    1: stockin_reserve_detail
    2: stockin_stockout_detail
    3: stockout_order_detail
KEY:
    primary key
example
php/bin/php AppendStatisticsOrder.php --type=1 --table=1 --key=1801010000001
sync create reserve stockin order, stockin_order_id=1801010000001

EOF;
        echo $strTips;
    }

    /**
     * type
     * @var int
     */
    private $intType;

    /**
     * table
     * @var int
     */
    private $intTable;

    /**
     * key
     * @var int
     */
    private $intKey;

    /**
     * opts
     * @var array
     */
    const OPTS =[
        'type::',
        'table::',
        'key::',
    ];

    public function getParams() {
        $arrOption = getopt('', self::OPTS);
        Bd_Log::trace('user input params: ' . json_encode($arrOption));
        $this->intType = intval($arrOption['type']);
        $this->intTable =intval($arrOption['table']);
        $this->intKey = intval($arrOption['key']);
    }
}
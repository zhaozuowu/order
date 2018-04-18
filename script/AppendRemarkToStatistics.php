#!php/bin/php
<?php
/**
 * @name AppendRemarkToStatistics
 * @desc app statistics order
 * @author lvbochao@iwaimai.baidu.com
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new AppendRemarkToStatistics();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendRemarkToStatistics
{

    /**
     * limit
     */
    const LIMIT = 20;

    /**
     * work
     */
    public function work()
    {
        $this->workStockin();
        $this->workStockout();
    }

    private function workStockin()
    {
        $arrSearchCondition = [
            'stockin_order_remark' => ['!=', ''],
        ];
        $intLimit = self::LIMIT;
        $arrFields = [
            'stockin_order_id',
            'stockin_order_type',
            'stockin_order_remark',
        ];
        $arrOrder = [
            'id' => 'asc',
        ];
        $intOffset = 0;
        $intStockinOrderId = 0;
        do {
            $arrRows = Model_Orm_StockinOrder::findRows($arrFields, $arrSearchCondition, $arrOrder, $intOffset, $intLimit);
            $intCount = count($arrRows);
            $intOffset += $intCount;
            foreach ($arrRows as $arrRow)
            {
                // start update
                $strRemark = $arrRow['stockin_order_remark'];
                $intStockinOrderId = $arrRow['stockin_order_id'];
                /**
                 * @var Order_Base_Orm $strOrm
                 */
                $strOrm = self::STOCKIN_ORDER_TYPE_ORM[$arrRow['stockin_order_type']];
                if (empty($strOrm)) {
                    Bd_Log::warning('type error! type: ' . intval($arrRow['stockin_order_type']));
                    continue;
                }
                $strOrm::updateAll(['stockin_order_remark' => $strRemark], ['stockin_order_id' => $intStockinOrderId]);
            }
            Bd_Log::trace('one batch stockin order sync finish. last stockin order id: ' . $intStockinOrderId);
            sleep(1);
        } while (!empty($intCount));
        Bd_Log::trace('/******************************WORK STOCKIN STATISTICS FINISH!******************************/');
    }

    /**
     * stockin order type orm
     */
    const STOCKIN_ORDER_TYPE_ORM = [
        Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE => 'Model_Orm_StockinReserveDetail',
        Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT => 'Model_Orm_StockinStockoutDetail',
    ];

    private function workStockout()
    {
        $arrSearchCondition = [
            'stockout_order_remark' => ['!=', ''],
        ];
        $intLimit = self::LIMIT;
        $arrFields = [
            'stockout_order_id',
            'stockout_order_remark',
        ];
        $arrOrder = [
            'id' => 'asc',
        ];
        $intOffset = 0;
        $intStockoutOrderId = 0;
        do {
            $arrRows = Model_Orm_StockoutOrder::findRows($arrFields, $arrSearchCondition, $arrOrder, $intOffset, $intLimit);
            $intCount = count($arrRows);
            $intOffset += $intCount;
            foreach ($arrRows as $arrRow)
            {
                // start update
                $strRemark = $arrRow['stockout_order_remark'];
                $intStockoutOrderId = $arrRow['stockout_order_id'];
                Model_Orm_StockoutOrderDetail::updateAll(['stockout_order_remark' => $strRemark],
                    ['stockout_order_id' => $intStockoutOrderId]);
            }
            Bd_Log::trace('one batch stockout order sync finish. last stockout order id: ' . $intStockoutOrderId);
            sleep(1);
        } while (!empty($intCount));
        Bd_Log::trace('/******************************WORK STOCKOUT STATISTICS FINISH!******************************/');
    }
}
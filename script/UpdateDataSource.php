<?php
/**
 * @name UpdateDataSource
 * @desc update data source to business
 * @author lvbochao@iwaimai.baidu.com
 */
Bd_Init::init();
    $objUpdateDataSource = new UpdateDataSource();
    $objUpdateDataSource->run();
    Bd_Log::trace('complete');
try {

} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}



class UpdateDataSource
{
    const LIMIT = 100;
    /**
     * run
     */
    public function run()
    {
        // start time
        $intStartTime = time();
        $arrCondition = [
            'data_source' => 0,
            'create_time' => ['<', $intStartTime]
        ];
        $arrOrderBy = [
            'id' => 'asc',
        ];
        $intOffset = 0;
        $arrNewColumn = [
            'data_source' => Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_SYSTEM_ORDER,
        ];
        $arrAllIds = Model_Orm_StockoutOrder::findColumn('id', $arrCondition, $arrOrderBy);
        $intTotal = count($arrAllIds);
        Bd_Log::trace(sprintf('all ids total[%d]', $intTotal));
        do {
            $arrIds = array_slice($arrAllIds, $intOffset, self::LIMIT);
            if (empty($arrIds)) {
                break;
            }
            $arrUpdateCondition = [
                'id' => ['in', $arrIds],
            ];
            Model_Orm_StockoutOrder::updateAll($arrNewColumn, $arrUpdateCondition);
            Bd_Log::trace(sprintf('batch update finish. id:[%s], offset[%d], limit[%d], real[%d], total[%d]',
                implode(', ', $arrIds), $intOffset, self::LIMIT, count($arrIds), $intTotal));
            $intOffset += self::LIMIT;
            sleep(1);
        } while (!empty($arrIds));
    }
}
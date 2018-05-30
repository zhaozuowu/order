<?php
/**
 * @name FixOrderSkuUpcUnit.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/19 17:43
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new FixOrderSkuUpcUnit();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class FixOrderSkuUpcUnit
{
    const LIMIT = 100;
    const MIN_UNIT_NUM = 1;
    public function work()
    {
        $arrConf = [
            Model_Orm_ReserveOrderSku::class,
            Model_Orm_StockinOrderSku::class,
        ];
        $intLimit = self::LIMIT;
        /**
         * @var Model_Orm_StockinOrderSku|Model_Orm_ReserveOrderSku $orm
         */
        foreach ($arrConf as $orm)
        {
            $arrCond = [
                'upc_unit_num' => ['!=', self::MIN_UNIT_NUM],
            ];
            $arrFields = [
                'sku_id',
                'upc_unit',
                'upc_unit_num',
            ];
            $arrResult = $orm::find($arrCond)->distinct()->select($arrFields)->rows();
            $intTotal = count($arrResult);
            $i = 0;
            // all sku info
            $arrSkuIds = array_map(function ($row){
                return $row['sku_id'];
            }, $arrResult);
            $arrSkuIds = array_unique($arrSkuIds);
            $ralSku = new Dao_Ral_Sku();
            $arrSkuInfo = $ralSku->getSkuInfos($arrSkuIds);
            $arrSkuUpcLib = [];
            foreach ($arrSkuInfo as $arrSku) {
                foreach ($arrSku['upcs'] as $arrUpc) {
                    $arrSkuUpcLib[$arrSku['sku_id']][$arrUpc['upc_unit_num']] = $arrUpc['upc_unit'];
                }
            }
            foreach ($arrResult as $arrDbRow) {
                $arrDbSkuId = $arrDbRow['sku_id'];
                $arrDbUpcUnitNum = $arrDbRow['upc_unit_num'];
                $intRightUpcUnit = $arrSkuUpcLib[$arrDbSkuId][$arrDbUpcUnitNum];
                Bd_Log::trace(sprintf('sku_id[%d], upc_unit_num[%d], right_upc_unit[%d], db_upc_unit[%d] [%d/%d]',
                    $arrDbSkuId, $arrDbUpcUnitNum, $intRightUpcUnit, $arrDbRow['upc_unit'], $i, $intTotal));
                if ($arrDbRow['upc_unit'] != $intRightUpcUnit && !empty($intRightUpcUnit)) {
                    Bd_Log::trace('update them');
                    // update relation sku
                    $arrCondUpdate = $arrDbRow;
                    // get all id
                    $arrIds = $orm::findColumn('id', $arrCondUpdate);
                    // update
                    $intOffset = 0;
                    $arrFields = [
                        'upc_unit' => $intRightUpcUnit,
                    ];
                    do {
                        $arrUpdateIds = array_slice($arrIds, $intOffset, $intLimit);
                        if (empty($arrUpdateIds)) {
                            break;
                        }
                        $arrUpdateCondition = ['id' => ['in', $arrUpdateIds]];
                        $orm::updateAll($arrFields, $arrUpdateCondition);
                        Bd_Log::trace('SCRIPT_UPDATE_SKU_ID update ids: ' . json_encode($arrUpdateIds));
                        $intCount = count($arrUpdateIds);
                        $intOffset += $intCount;
                        sleep(1);
                    } while ($intLimit <= $intCount);
                } else {
                    Bd_Log::trace('do not update');
                }
                $i++;
            }


        }
        $intLimit = self::LIMIT;
        $arrCond = [
            'upc_unit_num' => ['!=', self::MIN_UNIT_NUM],
        ];
        $arrFields = [
            'sku_id',
            'upc_unit',
            'upc_unit_num',
        ];
        $arrResult = Model_Orm_ReserveOrderSku::find($arrCond)->distinct()->select($arrFields)->rows();
        $intTotal = count($arrResult);
        $i = 0;
        // all sku info
        $arrSkuIds = array_map(function ($row){
            return $row['sku_id'];
        }, $arrResult);
        $arrSkuIds = array_unique($arrSkuIds);
        $ralSku = new Dao_Ral_Sku();
        $arrSkuInfo = $ralSku->getSkuInfos($arrSkuIds);
        $arrSkuUpcLib = [];
        foreach ($arrSkuInfo as $arrSku) {
            foreach ($arrSku['upcs'] as $arrUpc) {
                $arrSkuUpcLib[$arrSku['sku_id']][$arrUpc['upc_unit_num']] = $arrUpc['upc_unit'];
            }
        }
        foreach ($arrResult as $arrDbRow) {
            $arrDbSkuId = $arrDbRow['sku_id'];
            $arrDbUpcUnitNum = $arrDbRow['upc_unit_num'];
            $intRightUpcUnit = $arrSkuUpcLib[$arrDbSkuId][$arrDbUpcUnitNum];
            Bd_Log::trace(sprintf('sku_id[%d], upc_unit_num[%d], right_upc_unit[%d], db_upc_unit[%d] [%d/%d]',
                $arrDbSkuId, $arrDbUpcUnitNum, $intRightUpcUnit, $arrDbRow['upc_unit'], $i, $intTotal));
            if ($arrDbRow['upc_unit'] != $intRightUpcUnit && !empty($intRightUpcUnit)) {
                Bd_Log::trace('update them');
                // update relation sku
                $arrCondUpdate = $arrDbRow;
                // get all id
                $arrIds = Model_Orm_ReserveOrderSku::findColumn('id', $arrCondUpdate);
                // update
                $intOffset = 0;
                $arrFields = [
                    'upc_unit' => $intRightUpcUnit,
                ];
                do {
                    $arrUpdateIds = array_slice($arrIds, $intOffset, $intLimit);
                    if (empty($arrUpdateIds)) {
                        break;
                    }
                    $arrUpdateCondition = ['id' => ['in', $arrUpdateIds]];
                    Model_Orm_ReserveOrderSku::updateAll($arrFields, $arrUpdateCondition);
                    Bd_Log::trace('SCRIPT_UPDATE_SKU_ID update ids: ' . json_encode($arrUpdateIds));
                    $intCount = count($arrUpdateIds);
                    $intOffset += $intCount;
                    sleep(1);
                } while ($intLimit <= $intCount);
            } else {
                Bd_Log::trace('do not update');
            }
            $i++;
            
        }
        Bd_Log::trace('run complete');
    }
}
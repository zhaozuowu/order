#!php/bin/php
<?php
/**
 * @name AppendFromCountry
 * @desc append from country to reserve_order_sku and stockin_order_sku
 * @author lvbochao@iwaimai.baidu.com
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new AppendFromCountry();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendFromCountry
{

    /**
     * limit
     */
    const LIMIT = 20;

    /**
     * work
     * @throws Nscm_Exception_Error
     */
    public function work()
    {
        $intNow = time();
        $this->workTable('Model_Orm_StockinOrderSku', $intNow);
        $this->workTable('Model_Orm_ReserveOrderSku', $intNow);
    }

    /**
     * work table
     * @param Model_Orm_StockinOrderSku|Model_Orm_ReserveOrderSku $orm
     * @param int $intTime
     * @throws Nscm_Exception_Error
     */
    private function workTable($orm, $intTime)
    {
        $limit = 100;
        $arrCondiion = [
            'create_time' => ['<', $intTime],
            'sku_from_country' => ['!=', 0],
        ];
        // get all sku id
        $allSkuId = $orm::find($arrCondiion)->select(['sku_id'])->distinct()->column();
        Bd_log::trace('SCRIPT_UPDATE_SKU_ID all sku_id: ' . json_encode($allSkuId));
        $daoSku = new Dao_Ral_Sku();
        $arrSkuInfos = $daoSku->getSkuInfos($allSkuId);
        if (count($allSkuId) != count($arrSkuInfos)) {
            Bd_Log::warning('some sku info can`t be get. sku_ids: ' . json_encode(array_diff($allSkuId, array_keys($arrSkuInfos))));
        }
        $intSkuCount = count($arrSkuInfos);
        $i = 0;
        foreach ($arrSkuInfos as $intSkuId => $arrSkuInfo) {
            $i++;
            // get one sku
            $arrIds = $orm::findColumn('id', ['sku_id' => $intSkuId]);
            Bd_Log::trace(sprintf('SCRIPT_UPDATE_SKU_ID:[%d/%d] sku_id: %d', $i, $intSkuCount, $intSkuId));
            $intOffset = 0;
            $arrFields = [
                'sku_from_country' => $arrSkuInfo['sku_from_country'],
            ];
            do {
                $arrUpdateIds = array_slice($arrIds, $intOffset, $limit);
                if (empty($arrUpdateIds)) {
                    break;
                }
                $arrUpdateCondition = ['id' => ['in', $arrUpdateIds]];
                $orm::updateAll($arrFields, $arrUpdateCondition);
                Bd_Log::trace('SCRIPT_UPDATE_SKU_ID update ids: ' . json_encode($arrUpdateIds));
                $intCount = count($arrUpdateIds);
                $intOffset += $intCount;
                sleep(1);
            } while ($limit <= $intCount);
        }
        Bd_Log::trace('****************FINISH_TABLE:' . $orm);

    }

}
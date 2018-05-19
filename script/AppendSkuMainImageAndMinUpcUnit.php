#!php/bin/php
<?php
/**
 * @name AppendSkuMainImageAndMinUpcUnit
 * @desc wash data script that on skus of table reserve_order_sku, stockin_order_sku
 * on column [sku_main_image, upc_min_unit] data add
 * @author chenwende@iwaimai.baidu.com
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new AppendSkuMainImageAndMinUpcUnit();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendSkuMainImageAndMinUpcUnit
{
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
        $arrCondition = [
            'create_time' => ['<', $intTime],
        ];
        // get all sku id
        $arrAllSkuId = $orm::find($arrCondition)->select(['sku_id'])->distinct()->column();

        Bd_log::trace('SCRIPT_UPDATE_SKU_ID all sku_id: ' . json_encode($arrAllSkuId));

        printf("\n There are [ %d ] sku to process ..\n", count($arrAllSkuId));
        printf("\n Fetch all sku info ...\n");
        $daoSku = new Dao_Ral_Sku();
        $arrSkuInfosAll = $daoSku->getSkuInfos($arrAllSkuId);
        if (count($arrAllSkuId) != count($arrSkuInfosAll)) {
            Bd_Log::warning('some sku info can`t get. sku_ids: ' . json_encode(array_diff($arrAllSkuId,
                    array_keys($arrSkuInfosAll))));
            printf("\n WARNING: get skus not match, source skus [%d], get skus[%d] \n",
                count($arrAllSkuId), count($arrSkuInfosAll));
        }

        // parse sku_main_images out
        $intTotalCount = count($arrSkuInfosAll);
        printf("\n There are [ %d ] sku infos get after query ..\n", count($arrSkuInfosAll));
        $i = 0;
        $arrSkuInfos = [];
        printf("\n pre-processing all sku infos get ...\n");
        foreach ($arrSkuInfosAll as $row) {
            foreach ($row['sku_image'] as $rowImage) {
                if (true == $rowImage['is_master']) {
                    $row['after_pre_processor_sku_main_image_url'] = strval($rowImage['url']);
                    $row['after_pre_processor_sku_upc_min_unit'] =
                        intval($arrSkuInfosAll[$row['sku_id']]['min_upc']['upc_unit']);
                    break;
                }
            }
            $i++;
            printf("progress: [%-50s] %d%%\r", str_repeat('#', $i * 50 / $intTotalCount),
                $i * 100 / $intTotalCount);
            $arrSkuInfos[$row['sku_id']] = $row;
        }

        printf("\n\n writing all sku info...\n");
        printf("\n There are [ %d ] sku infos to write into db ..\n", count($arrSkuInfos));
        $intSkuCount = count($arrSkuInfos);
        $intTotalCount = $intSkuCount;
        $i = 0;
        foreach ($arrSkuInfos as $intSkuId => $arrSkuInfo) {
            $i++;
            // get one sku
            $arrIds = $orm::findColumn('id', ['sku_id' => $intSkuId]);
            Bd_Log::trace(sprintf('SCRIPT_UPDATE_SKU_ID:[%d/%d] sku_id: %d', $i, $intSkuCount, $intSkuId));
            $intOffset = 0;
            $arrFields = [
                'sku_main_image' => $arrSkuInfo['after_pre_processor_sku_main_image_url'],
                'upc_min_unit' => $arrSkuInfo['after_pre_processor_sku_upc_min_unit'],
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
            printf("progress: [%-50s] %d%%\r", str_repeat('#', $i * 50 / $intTotalCount),
                $i * 100 / $intTotalCount);
        }
        Bd_Log::trace('****************FINISH_TABLE:' . $orm);
        printf("\n************************\n >*FINISH_TABLE*< : %s\n**********************************\n", $orm);

    }

}
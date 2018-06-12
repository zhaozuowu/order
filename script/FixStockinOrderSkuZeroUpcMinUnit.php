#!php/bin/php
<?php
/**
 * @name FixStockinOrderSkuZeroUpcMinUnit
 * @desc wash data script that on skus of table stockin_order_sku
 * on column [upc_min_unit], which is upc_min_unit == 0 that should not occur
 * @author chenwende@iwaimai.baidu.com
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new FixStockinOrderSkuZeroUpcMinUnit();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class FixStockinOrderSkuZeroUpcMinUnit
{
    const LIMIT_COUNT = 1000;

    /**
     * work
     * @throws Nscm_Exception_Error
     */
    public function work()
    {
        $intNow = time();
        $this->workTable($intNow);
    }

    /**
     * work table
     * @param int $intTime
     * @throws Nscm_Exception_Error
     */
    private function workTable($intTime)
    {
        $arrCondition = [
            'create_time' => ['<', $intTime],
            'upc_min_unit' => 0,
        ];

        $arrColumns = ['id', 'sku_id', 'stockin_order_id'];

        Bd_Log::trace('find all skus that [0 == stockin_order_sku.upc_min_unit]');

        $arrAllSkus = Model_Orm_StockinOrderSku::findRows($arrColumns, $arrCondition);
        $intTotalCount = count($arrAllSkus);

        // convert to id-sku map
        $arrAllSkusIdMap = [];
        foreach ($arrAllSkus as $sku) {
            $arrAllSkusIdMap[$sku['id']] = $sku;
        }

        $arrAllId =
            array_values(
                    array_unique(
                            array_column($arrAllSkus, 'id')
                    )
            );

        $arrAllSkuId =
            array_values(
                array_unique(
                    array_column($arrAllSkus, 'sku_id')
            )
        );

        Bd_log::trace('SCRIPT_UPDATE_SKU_ID all sku_id: ' . json_encode($arrAllSkus));

        printf("\n There are [ %d ] recorded sku to process ..\n", $intTotalCount);
        printf("\n Therer are [%d] skus in total ...\n", count($arrAllSkuId));
        printf("\n Fetch all sku info ...\n");

        $daoSku = new Dao_Ral_Sku();
        $arrSkuInfosAll = $daoSku->getSkuInfos($arrAllSkuId);
        if (count($arrAllSkuId) != count($arrSkuInfosAll)) {
            Bd_Log::warning('some sku info can`t get. sku_ids: ' . json_encode(array_diff($arrAllSkuId,
                    array_keys($arrSkuInfosAll))));
            printf("\n WARNING: get skus not match, source skus [%d], get skus[%d] \n",
                count($arrAllSkuId), count($arrSkuInfosAll));
        }
        $arrSkuIdUpcMinUnitMap = [];
        foreach ($arrSkuInfosAll as $intSkuId => $skuInfo) {
            $arrSkuIdUpcMinUnitMap[$intSkuId] = $skuInfo['min_upc']['upc_unit'];
        }

        printf("\n There are [ %d ] sku infos get after query ..\n", count($arrSkuInfosAll));

        $i = 0;
        $intTotalCount = count($arrAllId);
        $intSuccess = 0;
        $intFailure = 0;
        $arrFailureIds = [];
        foreach ($arrAllId as $intId) {
            $i++;
            Order_Util::progressBar($i, $intTotalCount);
            $intSkuId = intval($arrAllSkusIdMap[$intId]['sku_id']);
            Bd_Log::trace(sprintf('SCRIPT_UPDATE_SKU_ID:[%d/%d] sku_id: %d', $i, $i, $intTotalCount, $intSkuId));

            $intSkuUpcMinUnit = $arrSkuIdUpcMinUnitMap[$intSkuId];
            if (empty($intSkuUpcMinUnit)) {
                Bd_Log::warning(sprintf('the sku_id[%d], id[%d] not found upc_min_unit in the map or is 0', $intSkuId, $intId));
                $intFailure++;
                $arrFailureIds[] = $intId;
                continue;
            }

            $arrFields = [
                'upc_min_unit' => $intSkuUpcMinUnit,
            ];
            $arrUpdateCondition = ['id' => $intId];
            Model_Orm_StockinOrderSku::updateAll($arrFields, $arrUpdateCondition);

            Bd_Log::trace('SCRIPT_UPDATE_STOCKIN_ORDER_SKU update id: ' . json_encode($intId));

            $intSuccess++;
            if (0 == ($i % self::LIMIT_COUNT)) {
                sleep(1);
            }
        }
        printf("\n************************ >*FINISH*< **********************************\n");
        printf("\n Success %d, Failed %d, Total %d", $intSuccess, $intFailure, $intTotalCount);

        if (!empty($intFailure)) {
//            printf("\n FAILED_IDS_LIST: %s", json_encode($arrFailureIds));
            Bd_Log::warning(sprintf("\n FAILED_STOCKIN_ORDER_SKU_IDS_LIST: %s", json_encode($arrFailureIds)));
        }
    }

}
#!php/bin/php
<?php
/**
 * Class AppendOrderSkuKindAmount
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/18 11:23
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $objAso = new AppendOrderSkuKindAmount();
    $objAso->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class AppendOrderSkuKindAmount
{
    /**
     * limit
     */
    const LIMIT = 100;

    /**
     * work
     */
    public function work()
    {
        $arrConf = [
            [
                'orm_class' => Model_Orm_ReserveOrder::class,
                'orm_sku_class' => Model_Orm_ReserveOrderSku::class,
                'order_column' => 'reserve_order_id',
                'update_column' => 'sku_kind_amount',
            ],
            [
                'orm_class' => Model_Orm_StockinOrder::class,
                'orm_sku_class' => Model_Orm_StockinOrderSku::class,
                'order_column' => 'stockin_order_id',
                'update_column' => 'sku_kind_amount',
            ],
        ];
        foreach ($arrConf as $arrOrmInfo) {
            /**
             * @var Order_Base_Orm $classOrmOrder
             */
            $classOrmOrder = $arrOrmInfo['orm_class'];
            /**
             * @var Order_Base_Orm $classOrmSku
             */
            $classOrmSku = $arrOrmInfo['orm_sku_class'];
            $strOrderColumn = $arrOrmInfo['order_column'];
            $strUpdateColumn = $arrOrmInfo['update_column'];
            $arrCond = [];
            $arrOrderBy = ['id' => 'asc'];
            $intLimit = self::LIMIT;
            $intOffset = 0;
            // once get order
            do {
                $arrOrderIds = $classOrmOrder::findColumn($strOrderColumn, $arrCond, $arrOrderBy, $intOffset,
                    $intLimit);
                $intCount = count($arrOrderIds);
                if (empty($intCount)) {
                    break;
                }
                $intOffset += $intCount;
                $arrSkuCond = [
                    $strOrderColumn => ['in', $arrOrderIds],
                ];
                $arrColumn = [
                    $strOrderColumn,
                    new Wm_Orm_Expression('count(*) as ' . $strUpdateColumn),
                ];
                $arrGroup = [$strOrderColumn];
                $arrCountInfo = $classOrmSku::find($arrSkuCond)->select($arrColumn)->groupBy($arrGroup)->rows();
                foreach ($arrCountInfo as $arrInfo) {
                    $arrFields = [
                        $strUpdateColumn => $arrInfo[$strUpdateColumn],
                    ];
                    $arrWhere = [
                        $strOrderColumn => $arrInfo[$strOrderColumn],
                    ];
                    $classOrmOrder::updateAll($arrFields, $arrWhere);
                }
                sleep(1);
            } while ($intCount >= $intLimit);
        }
    }
}
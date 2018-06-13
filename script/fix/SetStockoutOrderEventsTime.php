<?php
/**
 * @name SetStockoutOrderEventsTime
 * @desc 更新出库单事件(拣货/揽收/签收/作废)时间点
 * @author hang.song02@ele.me
 */

Bd_Init::init();

try {
    Bd_Log::trace(__FILE__ . ' script start run.');
    $startTime = time();
    echo sprintf("START:时间[%d],[%s]".PHP_EOL, time(), date('Y-m-d H:i:s'));
    $objAso = new SetStockoutOrderEventsTime();
    $objAso->work();
    $endTime = time();
    echo sprintf("END:时间[%d],[%s]".PHP_EOL, time(), date('Y-m-d H:i:s'));
    echo sprintf("总时间[%d]".PHP_EOL, ($endTime - $startTime));
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class SetStockoutOrderEventsTime
{
    const LIMIT = 100;

    public function work()
    {
        $arrConds = [
            'is_delete' => Nscm_Define_Const::ENABLE,
            'create_time' => ['<=', time()],
        ];
        $intOffset = 0;
        do {
            $arrStockOrderInfo = Model_Orm_StockoutOrder::findRows(['stockout_order_id'], $arrConds, ['id' => 'asc'], $intOffset, self::LIMIT);
            if (0 == count($arrStockOrderInfo)) {
                continue;
            }
            echo sprintf("OFFSET:[%d],时间[%d],[%s]" . PHP_EOL, $intOffset, time(), date('Y-m-d H:i:s'));
            Bd_Log::trace("STOCK_OUT_ORDER_OFFSET" . $intOffset);
            $arrStockoutOrderIds = array_column($arrStockOrderInfo, 'stockout_order_id');
            $arrLogList = $this->getLogListByConds($arrStockoutOrderIds);
            foreach ($arrStockoutOrderIds as $intStockoutOrderId) {
                if (!isset($arrLogList[$intStockoutOrderId])) {
                    echo sprintf("FAILED:log_not_existed_order_id[%d]" . PHP_EOL, $intStockoutOrderId);
                    continue;
                }
                $arrEventsTime = $this->getEventsTimeByLogList($arrLogList[$intStockoutOrderId], $intStockoutOrderId);

                $ormStockoutOrder = Model_Orm_StockoutOrder::findOne(['stockout_order_id' => $intStockoutOrderId]);
                if (empty($ormStockoutOrder)) {
                    continue;
                }
                //不影响数据平台即时数据
                $arrEventsTime['update_time'] = $ormStockoutOrder->update_time;
                $ormStockoutOrder->update($arrEventsTime);
                Bd_Log::trace(sprintf("stockout_order_id[%d],success", $intStockoutOrderId));
                echo sprintf("SUCCESS:order_id[%d]" . PHP_EOL, $intStockoutOrderId);
            }
            $intOffset += self::LIMIT;
            //执行100条getLogList&updateTime休息0.3秒
            usleep(300000);
        } while (self::LIMIT == count($arrStockOrderInfo));
    }

    private function getEventsTimeByLogList($arrLogList, $intStockoutOrderId)
    {
        //初始化
        $arrEventsTime = [
            'stockout_order_finish_pickup_time' => 0,
            'stockout_order_delivery_time' => 0,
            'stockout_order_signup_time' => 0,
            'stockout_order_destroy_time' => 0,
        ];
        foreach ($arrLogList as $arrLogItem) {
            $intEventTime = $arrLogItem['create_time'];
            switch (mb_substr($arrLogItem['content'], 0, 4)) {
                case '完成拣货':
                    $arrEventsTime['stockout_order_finish_pickup_time'] = $intEventTime;
                    break;
                case '完成揽收':
                    if (!empty($arrEventsTime['stockout_order_delivery_time'])) {
                        $intAlreadyStoreDeleverTime = $arrEventsTime['stockout_order_delivery_time'];
                        $arrEventsTime['stockout_order_finish_pickup_time'] = min($intAlreadyStoreDeleverTime, $intEventTime);
                        $arrEventsTime['stockout_order_delivery_time'] = max($intAlreadyStoreDeleverTime, $intEventTime);
                    } else {
                        $arrEventsTime['stockout_order_delivery_time'] = $intEventTime;
                    }
                    break;
                case '完成签收':
                    $arrEventsTime['stockout_order_signup_time'] = $intEventTime;
                    break;
                case '作废出库':
                    $arrEventsTime['stockout_order_destroy_time'] = $intEventTime;
                    break;
                default:
                    Bd_Log::warning(sprintf("出库单号[%d],无操作类型的时间[%s]"), $intStockoutOrderId, json_encode($arrLogItem));
                    break;
            }
        }
        return $arrEventsTime;
    }

    public function getLogListByConds($arrStockoutOrderIds)
    {
        $arrLogListRes = [];
        $arrConds = [];
        $intCurrentTime = time();

        $intStartTime = date('Ymd', strtotime('- 6 month', $intCurrentTime))<'20171212'?strtotime('20171212'):strtotime('- 6 month', $intCurrentTime);
        $intEndTime = $intCurrentTime;

        $arrConds['create_time'] = ['between',$intStartTime,$intEndTime];
        $arrConds['quota_idx_int_1'] = ['in', $arrStockoutOrderIds];


        $arrLogList = Model_Orm_LogDetail::findRows(['*'], $arrConds);

        foreach ($arrLogList as $item) {
            $arrLogListRes[$item['quota_idx_int_1']][] = $item;
        }
        return $arrLogListRes;
    }

}
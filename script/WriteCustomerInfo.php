#!php/bin/php
<?php
/**
 * @name WriteStockinStockoutClientId
 * @desc swipe data
 * @author bochao.lv@ele.me
 */

Bd_Init::init();

try
{
    $objWork = new WriteCustomerInfo();
    $objWork->work();
} catch (Exception $e)
{
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class WriteCustomerInfo
{

    const LIMIT = 100;

    /**
     * work
     */
    public function work()
    {
        $arrSearchCondition = [
            'create_time' => ['<', time()],
            'stockin_order_type' => Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT,
            'customer_id' => '',
        ];
        $intLimit = self::LIMIT;
        $arrOrder = [
            'id' => 'asc',
        ];
        $intOffset = 0;
        $intStockinOrderId = 0;
        $arrAllStockinOrderIds = Model_Orm_StockinOrder::findColumn('stockin_order_id', $arrSearchCondition, $arrOrder);
        do {
            $arrStockinOrderIds = array_slice($arrAllStockinOrderIds, $intOffset, $intLimit);
            if (empty($arrStockinOrderIds)) {
                break;
            }
            $arrCondition = ['stockin_order_id' => ['in', $arrStockinOrderIds]];
            /**
             * @var Model_Orm_StockinOrder[]
             */
            $objStockinOrders = Model_Orm_StockinOrder::findAll($arrCondition, $arrOrder);
            $intCount = count($objStockinOrders);
            $intOffset += $intCount;
            foreach ($objStockinOrders as $objStockinOrder)
            {
                $intStockinOrderId = $objStockinOrder->stockin_order_id;
                $arrSourceInfo = json_decode($objStockinOrder->source_info, true);
                $objStockinOrder->customer_id = $arrSourceInfo['customer_id'];
                $objStockinOrder->customer_name = $arrSourceInfo['customer_name'];
                // update table stockin
                $objStockinOrder->update();
            }
            Bd_Log::trace('one batch swipe source_customer_info finish. last stockin order id: ' . $intStockinOrderId);
            sleep(1);
        } while (!empty($intCount));
        Bd_Log::trace('/******************************WORK SWIPE SUPPLIER FINISH!******************************/');
    }
}
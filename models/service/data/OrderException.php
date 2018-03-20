<?php
/**
 * @name Service_Data_OrderException
 * @desc order exception
 * User: bochao.lv
 * Date: 2018/3/12
 * Time: 16:50
 */

class Service_Data_OrderException
{
    /**
     * write exception
     * @param $arrExceptions
     * @throws Wm_Error
     */
    public function writeException($arrExceptions)
    {
        $arrDb = [];
        foreach ($arrExceptions as $arrException) {
            $arrDb[] = [
                'exception_id' => Nscm_Lib_IdGenerator::sequenceDateNumber(),
                'order_id' => intval($arrException['order_id']),
                'sku_id' => intval($arrException['sku_id']),
                'sku_name' => strval($arrException['sku_name']),
                'exception_type' => intval($arrException['exception_type']),
                'exception_type_concrete' => intval($arrException['exception_type_concrete']),
                'exception_level' => intval($arrException['exception_level']),
                'exception_info' => strval($arrException['exception_info']),
                'exception_time' => intval($arrException['exception_time']),
            ];
        }
        if (!empty($arrDb)) {
            Model_Orm_OrderException::batchInsert($arrDb);
        }
    }
}
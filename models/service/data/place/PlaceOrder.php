<?php
/**
 * @desc 上架单服务
 * @date 2018/5/3
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Service_Data_Place_PlaceOrder
{

    /**
     * 获取上架单状态统计
     * @return array
     */
    public function getPlaceOrderStatistics()
    {
        $arrRet = [];
        $arrData = Model_Orm_PlaceOrder::getPlaceOrderStatistics();

        $intTotal = 0;
        foreach (Order_Define_PlaceOrder::ALL_STATUS as $intStatus) {
            $intCount = $this->findItemFromArrayByField($arrData,'place_order_status',$intStatus)['place_order_status_count'] ?? 0;
            $intTotal += $intCount;
            $arrRet[]=[
                'place_order_status'    => $intStatus,
                'count'                 => $intCount,
                'place_order_show'      => Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[$intStatus]
            ];
        }

        $arrRet[]=[
            'place_order_status'    => Order_Define_PlaceOrder::STATUS_ALL,
            'count'                 => $intTotal,
            'place_order_show'      => Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[Order_Define_PlaceOrder::STATUS_ALL]
        ];
        return $arrRet;
    }

    /**
     *
     * @return object
     */
    public function findItemFromArrayByField($objArr, $strField, $strSearch)
    {
        foreach ($objArr as $item) {
            if (isset($item[$strField]) && $item[$strField] == $strSearch) {
                return $item;
            }
        }
        return null;
    }

}
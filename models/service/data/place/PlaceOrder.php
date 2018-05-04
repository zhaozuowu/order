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
        $arrRet  = [];
        $arrData = Model_Orm_PlaceOrder::getPlaceOrderStatistics();

        $intTotal = 0;
        foreach (Order_Define_PlaceOrder::ALL_STATUS as $intStatus) {
            $intCount = $this->findItemFromArrayByField($arrData, 'place_order_status', $intStatus)['place_order_status_count'] ?? 0;
            $intTotal += $intCount;
            $arrRet[] = [
                'place_order_status' => $intStatus,
                'count'              => $intCount,
                'place_order_show'   => Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[$intStatus]
            ];
        }

        $arrRet[] = [
            'place_order_status' => Order_Define_PlaceOrder::STATUS_ALL,
            'count'              => $intTotal,
            'place_order_show'   => Order_Define_PlaceOrder::PLACE_ORDER_STATUS_SHOW[Order_Define_PlaceOrder::STATUS_ALL]
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

    /**
     * 获取上架单列表
     * @param $arrField array
     * @param $arrCond array
     * @return array
     */
    public function getPlaceList($arrField ,$arrCond)
    {

    }

    /**
     * 获取上架单列表字段
     * @return array
     */
    public function getPlaceListField()
    {
        return [
            'place_order_id',
            'stockin_order_id',
            'stockin_order_type',
            'place_order_status',
            'vendor_id',
            'vendor_name',
            'is_defective',
            'create_time',
            'confirm_time',
        ];
    }


    /**
     * 获取上架单列表条件 、参数检验
     * @param $arrField array
     * @param $arrCond array
     * @return array
     */
    public function getPlaceListCond($arrInput)
    {
        $arrCond = [];
        if (isset($arrInput['place_order_id'])){
            $arrCond['place_order_id']=['like','%'.$arrInput['place_order_id']];
        }
        if(isset($arrInput['stockin_order_id'])){
            $arrCond['stockin_order_id']=['like','%'.$arrInput['stockin_order_id']];
        }
        if(isset($arrInput['vendor_id'])){
            $arrCond['vendor_id']=['=',intval($arrInput['vendor_id'])];
        }
        if(isset($arrInput['place_order_status'])){
            if (!is_array($arrInput['place_order_status'])){
                $arrInput['place_order_status'] = ['in',intval($arrInput['place_order_status'])];
            }
            $arrCond['place_order_status']=['in',$arrInput['place_order_status']];
        }

        if (false === Order_Util::verifyUnixTimeSpan(
                $arrInput['start'],
                $arrInput['end'])) {
            Order_BusinessError::throwException(
                Order_Error_Code::QUERY_TIME_SPAN_ERROR);
        }


        if(isset($arrInput['stockin_order_id'])){
            $arrCond['stockin_order_id']=['like','%'.$arrInput['stockin_order_id']];
        }
        if(isset($arrInput['stockin_order_id'])){
            $arrCond['stockin_order_id']=['like','%'.$arrInput['stockin_order_id']];
        }

    }

}
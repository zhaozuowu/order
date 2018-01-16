<?php
/**
 * @name Action_GetReserveOrderStatistics
 * @desc 获取预约单状态统计
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderStatistics extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct function
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Reserve_GetReserveOrderStatistics();
    }

    /**
     * format result, output data format process
     *
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [];
        $arrRetList = $arrRet;
        $intTotal = 0;
        if(!empty($arrRet)) {
            foreach ($arrRetList as $arrListItem) {
                $arrRoundResult = [];
                $arrRoundResult['reserve_order_status'] = empty($arrListItem['reserve_order_status']) ? 0 : intval($arrListItem['reserve_order_status']);
                $arrRoundResult['reserve_order_status_count'] = empty($arrListItem['reserve_order_status_count']) ? 0 : intval($arrListItem['reserve_order_status_count']);
                $intTotal += intval($arrRoundResult['reserve_order_status_count']);
                $arrFormatResult['list'][] = $arrRoundResult;
            }
        }

        // 计算总数统计
        $arrRoundResult = [];
        $arrRoundResult['reserve_order_status'] = 0;
        $arrRoundResult['reserve_order_status_count'] = $intTotal;
        $arrFormatResult['list'][] = $arrRoundResult;
        $userId = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['user_id'];
        $appId = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info')['system'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult, $userId, $appId);

        return $arrFormatResult;
    }
}
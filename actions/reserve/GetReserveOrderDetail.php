<?php
/**
 * @name Action_GeReserveOrderDetail
 * @desc 查询预约单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'regex|patern[/^ASN\d{13}$/]',
    ];

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
        $this->objPage = new Service_Page_Reserve_GetReserveOrderDetail();
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
        if (!empty($arrRet)) {
            $arrRoundResult = [];
            $arrRoundResult['reserve_order_id'] = empty($arrRet['reserve_order_id']) ? ''
                : Nscm_Define_OrderPrefix::ASN . strval($arrRet['reserve_order_id']);
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrRet['stockin_order_id']);
            $arrRoundResult['warehouse_id'] = empty($arrRet['warehouse_id']) ? ''
                : intval($arrRet['warehouse_id']);
            $arrRoundResult['warehouse_name'] = empty($arrRet['warehouse_name']) ? ''
                : strval($arrRet['warehouse_name']);
            $arrRoundResult['reserve_order_plan_time'] = empty($arrRet['reserve_order_plan_time']) ? 0
                : strval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($arrRet['reserve_order_plan_time']) ?? '未知';
            $arrRoundResult['reserve_order_status'] = empty($arrRet['reserve_order_status']) ? ''
                : intval($arrRet['reserve_order_status']);
            $arrRoundResult['reserve_order_remark'] = empty($arrRet['reserve_order_remark']) ? ''
                : strval($arrRet['reserve_order_remark']);
            $arrRoundResult['vendor_id'] = empty($arrRet['vendor_id']) ? ''
                : intval($arrRet['vendor_id']);
            $arrRoundResult['vendor_name'] = empty($arrRet['vendor_name']) ? ''
                : strval($arrRet['vendor_name']);

            $arrFormatResult = $arrRoundResult;
        }

        $intUserId = $this->arrSession['user_id'];
        $intAppId = $this->arrSession['system'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult, $intUserId, $intAppId);

        return $arrFormatResult;
    }
}
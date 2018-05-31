<?php
/**
 * @name Action_GetReserveOrderDetail
 * @desc 查询采购（预约）单详情
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderDetail extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_id' => 'regex|patern[/^(ASN|PUR)\d{13}$/]',
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
     * @throws Nscm_Exception_System
     * @throws Order_BusinessError
     */
    public function format($arrRet)
    {
        // 如果无仓库权限，则抛出异常
        if(false == boolval(!Nscm_Service_Auth::checkWarehouse([$arrRet['warehouse_id']]))) {
            Order_BusinessError::throwException(Order_Error_Code::USER_NO_WAREHOUSE_RIGHT);
        }

        $arrFormatResult = [];
        if (!empty($arrRet)) {
            $arrRoundResult = [];
            $arrRoundResult['reserve_order_id'] = empty($arrRet['reserve_order_id']) ? ''
                : Nscm_Define_OrderPrefix::ASN . strval($arrRet['reserve_order_id']);
            $arrRoundResult['stockin_order_id'] = empty($arrRet['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrRet['stockin_order_id']);
            $arrRoundResult['purchase_order_id'] = empty($arrRet['purchase_order_id'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : Nscm_Define_OrderPrefix::PUR . strval($arrRet['purchase_order_id']);
            $arrRoundResult['warehouse_id'] = empty($arrRet['warehouse_id']) ? 0
                : intval($arrRet['warehouse_id']);
            $arrRoundResult['warehouse_session_privilege'] =
                boolval(!Nscm_Service_Auth::checkWarehouse([$arrRet['warehouse_id']]));
            $arrRoundResult['warehouse_name'] =
                empty($arrRet['warehouse_name']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrRet['warehouse_name']);
            $arrRoundResult['reserve_order_plan_amount'] = empty($arrRet['reserve_order_plan_amount']) ? 0
                : intval($arrRet['reserve_order_plan_amount']);
            $arrRoundResult['reserve_order_plan_time'] = empty($arrRet['reserve_order_plan_time']) ? 0
                : intval($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($arrRet['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_status'] = empty($arrRet['reserve_order_status']) ? 0
                : intval($arrRet['reserve_order_status']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrRet['stockin_order_real_amount']) ? '--'
                : intval($arrRet['stockin_order_real_amount']);
            $arrRoundResult['reserve_order_remark'] = empty($arrRet['reserve_order_remark']) ? ''
                : strval($arrRet['reserve_order_remark']);
            $arrRoundResult['vendor_id'] = empty($arrRet['vendor_id']) ? 0
                : intval($arrRet['vendor_id']);
            $arrRoundResult['vendor_name'] =
                empty($arrRet['vendor_name']) ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                    : strval($arrRet['vendor_name']);
            $arrRoundResult['display_operate_tip'] = empty($arrRet['display_operate_tip']) ? false
                : boolval($arrRet['display_operate_tip']);
            $arrRoundResult['last_operate_time'] = intval($arrRet['last_operate_time']);
            $arrRoundResult['last_operate_name'] = empty($arrRet['last_operate_name'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['last_operate_name']);
            $arrRoundResult['last_operate_device'] = empty($arrRet['last_operate_device'])
                ? Order_Define_Const::DEFAULT_EMPTY_RESULT_STR
                : strval($arrRet['last_operate_device']);

            $arrFormatResult = $arrRoundResult;
        }

        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult);

        return $arrFormatResult;
    }
}
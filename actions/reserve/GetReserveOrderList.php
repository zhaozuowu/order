<?php
/**
 * @name Action_GetReserveOrderList
 * @desc 获取预约订单列表（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetReserveOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'reserve_order_status' => 'str|default[10,20,30,31]',
        'warehouse_ids' => 'str|required',
        'reserve_order_id' => 'regex|patern[/^(ASN\d{13})?$/]',
        'vendor_id' => 'int|min[0]',
        'create_time_start' => 'int|min[0]',
        'create_time_end' => 'int|min[0]',
        'reserve_order_plan_time_start' => 'int|min[0]|required',
        'reserve_order_plan_time_end' => 'int|min[0]|required',
        'stockin_time_start' => 'int|min[0]',
        'stockin_time_end' => 'int|min[0]',
        'page_num' => 'int|default[1]|min[1]|optional',
        'page_size' => 'int|required|min[1]|max[200]',
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
        $this->objPage = new Service_Page_Reserve_GetReserveOrderList();
    }

    /**
     * format result, output data format process
     * @param array $arrRet
     * @return array
     */
    public function format($arrRet)
    {
        $arrFormatResult = [
            'list' => [],
            'total' => 0,
        ];
        if (empty($arrRet['list'])) {
            return $arrFormatResult;
        }
        $arrRetList = $arrRet['list'];
        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['vendor_id'] = empty($arrListItem['vendor_id']) ? ''
                : intval($arrListItem['vendor_id']);
            $arrRoundResult['vendor_name'] = empty($arrListItem['vendor_name']) ? ''
                : strval($arrListItem['vendor_name']);
            $arrRoundResult['reserve_order_id'] = empty($arrListItem['reserve_order_id']) ? ''
                : Nscm_Define_OrderPrefix::ASN . strval($arrListItem['reserve_order_id']);
            $arrRoundResult['stockin_order_id'] = empty($arrListItem['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrListItem['stockin_order_id']);
            $arrRoundResult['reserve_order_status'] = empty($arrListItem['reserve_order_status']) ? 0
                : intval($arrListItem['reserve_order_status']);
            $arrRoundResult['warehouse_name'] = empty($arrListItem['warehouse_name']) ? ''
                : strval($arrListItem['warehouse_name']);
            $arrRoundResult['reserve_order_plan_time'] = empty($arrListItem['reserve_order_plan_time']) ? 0
                : intval($arrListItem['reserve_order_plan_time']);
            $arrRoundResult['reserve_order_plan_time_text'] =
                Order_Util::getFormatDateTime($arrListItem['reserve_order_plan_time']);
            $arrRoundResult['stockin_time'] = empty($arrListItem['stockin_time']) ? 0
                : intval($arrListItem['stockin_time']);
            $arrRoundResult['stockin_time_text'] =
                Order_Util::getFormatDateTime($arrListItem['stockin_time']);
            $arrRoundResult['reserve_order_plan_amount'] = empty($arrListItem['reserve_order_plan_amount']) ? 0
                : intval($arrListItem['reserve_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? 0
                : intval($arrListItem['stockin_order_real_amount']);
            $arrRoundResult['reserve_order_remark'] = empty($arrListItem['reserve_order_remark']) ? ''
                : strval($arrListItem['reserve_order_remark']);

            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];
        $intUserId = $this->arrSession['user_id'];
        $intAppId = $this->arrSession['system'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult, $intUserId, $intAppId);

        return $arrFormatResult;
    }
}
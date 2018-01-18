<?php
/**
 * @name Action_GetStockinOrderList
 * @desc 获取入库单列表（分页）
 * @author chenwende@iwaimai.baidu.com
 */

class Action_GetStockinOrderList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_type' => 'str|required',
        'stockin_order_id' => 'regex|patern[/^(SIO\d{13})?$/]',
        'warehouse_ids' => 'str|required',
        'source_supplier_id' => 'int|min[0]',
        'source_order_id' => 'regex|patern[/^((ASN|SOO)\d{13})?$/]',
        'vendor_id' => 'int|min[0]',
        'create_time_start' => 'int|min[0]',
        'create_time_end' => 'int|min[0]',
        'stockin_order_plan_time_start' => 'int|min[0]',
        'stockin_order_plan_time_end' => 'int|min[0]',
        'stockin_time_start' => 'int|min[0]|required',
        'stockin_time_end' => 'int|min[0]|required',
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
        $this->objPage = new Service_Page_Stockin_GetStockinOrderList();
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
        // 返回结果数据
        if (empty($arrRet['list'])) {
            return $arrFormatResult;
        }
        $arrRetList = $arrRet['list'];
        $strSourceOrderId = '';
        foreach ($arrRetList as $arrListItem) {
            $arrRoundResult = [];
            $arrRoundResult['stockin_order_type'] = empty($arrListItem['stockin_order_type']) ? ''
                : intval($arrListItem['stockin_order_type']);
            $arrRoundResult['source_info'] = empty($arrListItem['source_info']) ? ''
                : strval($arrListItem['source_info']);
            // 不同的入库单类型对应的前缀
            $intStockInType = intval($arrListItem['stockin_order_type']);
            if(!empty($intStockInType)) {
                if(Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_RESERVE == $intStockInType){
                    $strSourceOrderId = empty($arrListItem['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::ASN . strval($arrListItem['source_order_id']);
                }else if (Order_Define_StockinOrder::STOCKIN_ORDER_TYPE_STOCKOUT == $intStockInType){
                    $strSourceOrderId = empty($arrListItem['source_order_id']) ? ''
                        : Nscm_Define_OrderPrefix::SOO . strval($arrListItem['source_order_id']);
                }
            }
            $arrRoundResult['source_order_id'] = $strSourceOrderId;
            $arrRoundResult['stockin_order_id'] = empty($arrListItem['stockin_order_id']) ? ''
                : Nscm_Define_OrderPrefix::SIO . strval($arrListItem['stockin_order_id']);
            $arrRoundResult['stockin_order_status'] = empty($arrListItem['stockin_order_status']) ? ''
                : intval($arrListItem['stockin_order_status']);
            $arrRoundResult['warehouse_name'] = empty($arrListItem['warehouse_name']) ? ''
                : strval($arrListItem['warehouse_name']);
            $arrRoundResult['stockin_time'] = empty($arrListItem['stockin_time']) ? 0
                : strval($arrListItem['stockin_time']);
            $arrRoundResult['stockin_time_text'] =
                Order_Util::getFormatDateTime($arrListItem['stockin_time']) ?? '未知';
            $arrRoundResult['stockin_order_plan_amount'] = empty($arrListItem['stockin_order_plan_amount']) ? ''
                : strval($arrListItem['stockin_order_plan_amount']);
            $arrRoundResult['stockin_order_real_amount'] = empty($arrListItem['stockin_order_real_amount']) ? ''
                : strval($arrListItem['stockin_order_real_amount']);
            $arrRoundResult['stockin_order_remark'] = empty($arrListItem['stockin_order_remark']) ? ''
                : strval($arrListItem['stockin_order_remark']);
            $arrRoundResult['create_time'] = empty($arrListItem['create_time']) ? 0
                : strval($arrListItem['create_time']);
            $arrRoundResult['create_time_text'] =
                Order_Util::getFormatDateTime($arrListItem['create_time']) ?? '未知';
            $arrRoundResult['stockin_order_creator_name'] = empty($arrListItem['stockin_order_creator_name']) ? ''
                : strval($arrListItem['stockin_order_creator_name']);

            $arrFormatResult['list'][] = $arrRoundResult;
        }

        $arrFormatResult['total'] = $arrRet['total'];
        $intUserId = $this->arrSession['user_id'];
        $intAppId = $this->arrSession['system'];
        Nscm_Service_Format_Data::filterIllegalData($arrFormatResult, $intUserId, $intAppId);
        return $arrFormatResult;
    }
}
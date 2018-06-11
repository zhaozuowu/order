<?php
/**
 * @name Action_Api_GetBusinessFormOrderByIds
 * @desc 查询业态订单明细
 * @author  huabang.xue@ele.me
 */

class Action_Service_GetBusinessFormOrderByIdsService extends Order_Base_ServiceAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'business_form_order_ids' => 'regex|patern[/\d+/]',
        'filter'                  => 'regex|patern[/[a-z_]+(\,[a-z_]*)*/]',
        'is_md5'                  => 'int|max[1]|min[0]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * init object
     */
    public function myConstruct()
    {

        $this->objPage = new Service_Page_Business_Api_GetBusinessFormOrderByids();
    }

    /**
     * format result
     * @param array $arrData
     * @return array
     */
    public function format($arrData)
    {
        $arrRet = [];
        if (empty($arrData)) {
            return $arrRet;
        }
        $arrList = [];
        foreach ($arrData as $arrItem) {
            $arrFormatItem = [
                'business_form_order_id' => empty($arrItem['business_form_order_id'])
                    ? 0 : intval($arrItem['business_form_order_id']),
                'business_form_status' => empty($arrItem['status']) ? 0 : intval($arrItem['status']),
                'business_form_order_status_text' => empty($arrItem['status'])
                    ? '' : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_STATUS_LIST[$arrItem['status']],
                'business_form_order_type' => empty($arrItem['business_form_order_type'])
                    ? 0 : intval($arrItem['business_form_order_type']),
                'business_form_order_type_text' => empty($arrItem['business_form_order_type']) ? ''
                    : Order_Define_BusinessFormOrder::BUSINESS_FORM_ORDER_TYPE_LIST[$arrItem['business_form_order_type']],
                'create_time' => empty($arrItem['create_time']) ? 0 : date('Y-m-d H:i:s', $arrItem['create_time']),
                'order_amount' => empty($arrItem['order_amount']) ? 0 : intval($arrItem['order_amount']),
                'business_form_order_remark' => empty($arrItem['business_form_order_remark'])
                    ? '' : $arrItem['business_form_order_remark'],
                'warehouse_name' => empty($arrItem['warehouse_name']) ? '' : $arrItem['warehouse_name'],
                'customer_id' => empty($arrItem['customer_id']) ? '' : $arrItem['customer_id'],
                'customer_name' => empty($arrItem['customer_name']) ? '' : $arrItem['customer_name'],
                'customer_address' => empty($arrItem['customer_address']) ? '' : $arrItem['customer_address'],
                'customer_contactor' => empty($arrItem['customer_contactor']) ? '' : $arrItem['customer_contactor'],
                'customer_contact' => empty($arrItem['customer_contact']) ? '' : $arrItem['customer_contact'],
                'executor' => empty($arrItem['executor']) ? '' : $arrItem['executor'],
                'executor_contact' => empty($arrItem['executor_contact']) ? '' : $arrItem['executor_contact'],
            ];
            $arrList[] = $arrFormatItem;
        }

        $arrFilter = explode(',', $this->arrFilterResult['filter']);
        sort($arrFilter);
        $arrRes = [];
        foreach ($arrList as $arrItem) {
            $arrTemp = [];
            foreach ($arrFilter as $strKey) {
                if (array_key_exists($strKey, $arrItem)) {
                    $arrTemp[$strKey] = $arrItem[$strKey];
                }
            }
            if (!empty($arrTemp)) {
                $arrRes[] = $arrTemp;
            }
        }

        if (!empty($this->arrFilterResult['is_md5']) && count($arrRes) > 0) {
            $arrSortIds = array_column($arrRes, 'business_form_order_id');
            array_multisort($arrSortIds, SORT_ASC, $arrRes);
            $arrRet = md5(json_encode($arrRes));
            return $arrRet;
        }

        $arrRet['total'] = count($arrRes);
        $arrRet['list']  = $arrRes;
        return $arrRet;
    }


}
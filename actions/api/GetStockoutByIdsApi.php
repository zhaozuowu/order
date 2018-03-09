<?php
/**
 * @name Action_GetStockoutByIdApi
 * @desc 查询出库单明细
 * @author  huabang.xue@ele.me
 */

class Action_GetStockoutByIdsApi extends Order_Base_ApiAction
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_ids' => 'regex|patern[/SOO\d{13}(\,SSO\d{13})*/]',
        'filter'             => 'regex|patern[/[a-z_]+(\,[a-z_]*)*/]',
        'is_md5'             => 'int|max[1]|min[0]',
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
        $this->objPage = new Service_Page_Stockout_Api_GetStockoutByIdsApi();
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $arrRet = [];
        if (empty($data)) {
            return $arrRet;
        }
        $arrData = $data;
        $arrList = [];
        foreach ($arrData as $arrItem) {
            $arrList[] = array(
                'stockout_order_id' => empty($arrItem['stockout_order_id'])
                    ? '' : Nscm_Define_OrderPrefix::SOO . $arrItem['stockout_order_id'],
                'stockout_order_status' => empty($arrItem['stockout_order_status'])
                    ? 0 : $arrItem['stockout_order_status'],
                'stockout_order_status_text' => empty($arrItem['stockout_order_status'])
                    ? '' : Order_Define_StockoutOrder::STOCK_OUT_ORDER_STATUS_LIST[$arrItem['stockout_order_status']],
                'business_form_order_id' => empty($arrItem['business_form_order_id'])
                    ? 0 : intval($arrItem['business_form_order_id']),
                'warehouse_id' => empty($arrItem['warehouse_id']) ? 0 : $arrItem['warehouse_id'],
                'warehouse_session_privilege' => boolval(!Nscm_Service_Auth::checkWarehouse([$arrItem['warehouse_id']])),
                'warehouse_name' => empty($arrItem['warehouse_name']) ? '' : $arrItem['warehouse_name'],
                'stockout_order_type' => empty($arrItem['stockout_order_type'])
                    ? '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_TYPE_LIST[$arrItem['stockout_order_type']],
                'stockout_order_source' => empty($arrItem['stockout_order_source'])
                    ? '' : Order_Define_StockoutOrder::STOCKOUT_ORDER_SOURCE_LIST[$arrItem['stockout_order_source']],
                'stockout_create_time' => empty($arrItem['create_time'])
                    ? 0 : date('Y-m-d H:i:s', $arrItem['create_time']),
                'stockout_update_time' => empty($arrItem['update_time'])
                    ? 0 : date('Y-m-d H:i:s', $arrItem['update_time']),
                'stockout_expect_send_time' => date('Y-m-d H:i:s', $arrItem['expect_arrive_start_time'])
                    . "~" . date('Y-m-d H:i:s', $arrItem['expect_arrive_end_time']),
                'stockout_order_amount' => empty($arrItem['stockout_order_amount'])
                    ? 0 : $arrItem['stockout_order_amount'],
                'stockout_order_distribute_amount' => empty($arrItem['stockout_order_distribute_amount'])
                    ? 0 : $arrItem['stockout_order_distribute_amount'],
                'stockout_order_pickup_amount' => empty($arrItem['stockout_order_pickup_amount'])
                    ? 0 : $arrItem['stockout_order_pickup_amount'],
                'stockout_order_remark' => empty($arrItem['stockout_order_remark'])
                    ? '' : $arrItem['stockout_order_remark'],
                'signup_status' => empty($arrItem['signup_status'])
                    ? '' : Order_Define_StockoutOrder::STOCKOUT_SIGINUP_STATUS_LIST[$arrItem['signup_status']],
                'executor' => empty($arrItem['executor']) ? '' : $arrItem['executor'],
                'executor_contact' => empty($arrItem['executor_contact']) ? '' : $arrItem['executor_contact'],
            );
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
            $arrRes[] = $arrTemp;
        }

        if (!empty($this->arrFilterResult['is_md5']) && count($arrRes) > 0) {
            $arrSortIds = array_column($arrRes, 'stockout_order_id');
            sort($arrSortIds);
            array_multisort($arrSortIds, SORT_ASC, SORT_STRING, $arrRes);
            $arrRet = md5(json_encode($arrRes));
            return $arrRet;
        }

        $arrRet['total'] = count($arrRes);
        $arrRet['list']  = $arrRes;
        return $arrRet;
    }

}
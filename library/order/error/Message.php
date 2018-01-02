<?php

/**
 * @name Order_Error_Message
 * @desc Error Code
 * @auth zhaozuowu@iwaimai.baidu.com
 */
class Order_Error_Message extends Wm_Error_Message
{


    protected $_disp_app_err_msg = [
        Order_Error_Code::SUCCESS => '',
        Order_Error_Code::RAL_ERROR => 'X',
        Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS => '出库单不存在',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE => '出库单状态不允许修改',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL => '出库订单更新失败',
        Order_Error_Code::QUERY_TIME_SPAN_ERROR => '查询时间范围错误',
        Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED => 'nscm采购单号已经被接收',
        Order_Error_Code::PURCHASE_ORDER_NOT_EXIST => '采购单不存在',
        Order_Error_Code::PURCHASE_ORDER_NOT_ALLOW_DESTROY => '订单状态不允许作废',
        Order_Error_Code::NSCM_PURCHASE_ORDER_HAS_BEEN_RECEIVED => 'nscm采购单号已经被接收',
        Order_Error_Code::NSCM_PURCHASE_ORDER_NOT_EXIST => '采购单不存在',
        Order_Error_Code::NSCM_PURCHASE_ORDER_NOT_ALLOW_DESTROY => '订单状态不允许作废',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL => '出库单创建失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR => '出库单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR => '业态订单补货类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR => '业态订单类型错误',
    ];

}

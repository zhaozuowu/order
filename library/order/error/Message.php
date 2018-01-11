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
        Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS => '出库单sku不存在',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE => '出库单状态不允许修改',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL => '出库订单更新失败',
        Order_Error_Code::NWMS_STOCKOUT_ADJUST_SKU_STOCK_FAIL=> '库存扣减失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL=>'仓库完成拣货失败',
        Order_Error_Code::QUERY_TIME_SPAN_ERROR => '查询时间范围错误',
        Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED => 'nscm采购单号已经被接收',
        Order_Error_Code::PURCHASE_ORDER_NOT_EXIST => '采购单不存在',
        Order_Error_Code::PURCHASE_ORDER_NOT_ALLOW_DESTROY => '订单状态不允许作废',
        Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST => '源订单不存在',
        Order_Error_Code::SKU_TOO_MUCH => '同一商品最多只能有两个效期',
        Order_Error_Code::STOCKIN_ORDER_AMOUNT_TOO_MUCH => '入库数量不能多于源订单数量',
        Order_Error_Code::SKU_ID_NOT_EXIST_OR_SKU_ID_REPEAT => '商品不存在或商品重复',
        Order_Error_Code::SOURCE_ORDER_TYPE_ERROR => '订单类型错误',
        Order_Error_Code::TABLE_NOT_EXIST => '映射表不存在',
        Order_Error_Code::ORM_NOT_EXIST => '映射orm不存在',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL => '出库单创建失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR => '出库单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR => '业态订单补货类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR => '业态订单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SKU_ID_EMPTY => '业态订单创建sku_id不能为空',
        Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL => '锁库存失败',
        Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL => '解冻库存失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR => '创建业态订单失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR => '创建业态订单参数错误',
        Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR => '打印列表无法获取数据ID',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_REPEAT_SUBMIT => '重复创建出库单',
    ];

}

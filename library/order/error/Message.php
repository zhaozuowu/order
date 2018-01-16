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
        Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN => '此单状态不允许入库',
        Order_Error_Code::WAREHOUSE_NOT_MATCH => '仓库不匹配',
        Order_Error_Code::ALL_SKU_MUST_STOCKIN => '全部商品必须入库',
        Order_Error_Code::SKU_AMOUNT_CANNOT_EMPTY => '商品数量不允许为0',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL => '出库单创建失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR => '出库单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR => '业态订单补货类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR => '业态订单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SKU_ID_EMPTY => '业态订单创建sku_id不能为空',
        Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL => '锁库存失败',
        Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL => '解冻库存失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR => '创建业态订单失败',
        Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL => '作废出库单失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR => '创建业态订单参数错误',
        Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR => '打印列表无法获取数据ID',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_REPEAT_SUBMIT => '重复创建出库单',
        Order_Error_Code::NWMS_ORDER_QUERY_RESULT_EMPTY => '查询结果为空',

        // 库存调整错误码 开始
        Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL => '库存调整-出库失败',
        Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR => '库存调整-sku效期类型不识别',
        Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR => '库存调整-sku id 不存在',
        Order_Error_Code::NWMS_ADJUST_TYPE_ERROR => '库存调整-调整类型不正确',
        Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR => '库存调整-调整类型数量不正确',
        Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR => '库存调整-获取用户信息（用户名、用户ID）失败',
        Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL => '查询商品库存信息失败',
        Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED => '获取商品信息失败',
        // 库存调整错误码 结束



        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_SKU_FAILED => '获取商品信息失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED => '获取仓储信息失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_CUSTOMER_REGION_ID_ERROR => '客户区域编号错误',
    ];

}

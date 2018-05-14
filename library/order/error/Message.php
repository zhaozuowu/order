<?php

/**
 * @name Order_Error_Message
 * @desc Error Code
 * @auth zhaozuowu@iwaimai.baidu.com
 */
class Order_Error_Message extends Wm_Error_Message
{
    protected $_disp_app_err_msg = [
        Order_Error_Code::SUCCESS                                            => '',
        Order_Error_Code::RAL_ERROR                                          => 'X',
        Order_Error_Code::INTERFACE_HAS_BEEN_DISCARDED                       => '此接口已废弃',
        Order_Error_Code::STOCKOUT_ORDER_NO_EXISTS                           => '出库单不存在',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS                  => '出库单sku不存在',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE             => '出库单状态不允许修改',
        Order_Error_Code::STOCKOUT_ORDER_STATUS_UPDATE_FAIL                  => '出库订单更新失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_SIGNUP_SKUS_NOT_EXISTS         => '签收数量不存在',
        Order_Error_Code::NWMS_STOCKOUT_ADJUST_SKU_STOCK_FAIL                => '库存扣减失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL             => '仓库完成拣货失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_FINISH_PICKUP_AMOUNT_ERROR     => '拣货数量有误',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_PRE_CANCEL_ERROR               => '预取消状态有误',
        Order_Error_Code::QUERY_TIME_SPAN_ERROR                              => '查询时间范围错误',
        Order_Error_Code::PURCHASE_ORDER_HAS_BEEN_RECEIVED                   => 'nscm采购单号已经被接收',
        Order_Error_Code::PURCHASE_ORDER_NOT_EXIST                           => '采购单不存在',
        Order_Error_Code::PURCHASE_ORDER_NOT_ALLOW_DESTROY                   => '订单状态不允许作废',
        Order_Error_Code::SOURCE_ORDER_ID_NOT_EXIST                          => '源订单不存在',
        Order_Error_Code::SKU_TOO_MUCH                                       => '同一商品最多只能有两个效期',
        Order_Error_Code::STOCKIN_ORDER_AMOUNT_TOO_MUCH                      => '入库数量不能多于源订单数量',
        Order_Error_Code::SKU_ID_NOT_EXIST_OR_SKU_ID_REPEAT                  => '商品不存在或商品重复',
        Order_Error_Code::SOURCE_ORDER_TYPE_ERROR                            => '订单类型错误',
        Order_Error_Code::TABLE_NOT_EXIST                                    => '映射表不存在',
        Order_Error_Code::ORM_NOT_EXIST                                      => '映射orm不存在',
        Order_Error_Code::RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN             => '此单状态不允许入库',
        Order_Error_Code::WAREHOUSE_NOT_MATCH                                => '仓库不匹配',
        Order_Error_Code::ALL_SKU_MUST_STOCKIN                               => '全部商品必须入库',
        Order_Error_Code::SKU_AMOUNT_CANNOT_EMPTY                            => '商品数量不允许为0',
        Order_Error_Code::TOTAL_COUNT_CANNOT_EMPTY                           => '商品入库总数不允许为0',
        Order_Error_Code::RESERVE_STOCKIN_SEND_WMQ_FAIL                      => '沧海系统内部错误',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_CREATE_FAIL                    => '出库单创建失败',
        Order_Error_Code::NWMS_STOCKOUT_ORDER_TYPE_ERROR                     => '出库单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR         => '业态订单补货类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR                => '业态订单类型错误',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_SKU_ID_EMPTY              => '业态订单创建sku_id不能为空',
        Order_Error_Code::NWMS_STOCKOUT_FREEZE_STOCK_FAIL                    => '锁库存失败',
        Order_Error_Code::NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL                  => '解冻库存失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR              => '创建业态订单失败：商品库存不足或商品库信息异常',
        Order_Error_Code::NWMS_STOCKOUT_CANCEL_STOCK_FAIL                    => '作废出库单失败',
        Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR              => '创建业态订单参数错误',
        Order_Error_Code::NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR              => '打印列表无法获取数据ID',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_REPEAT_SUBMIT            => '重复创建出库单',
        Order_Error_Code::TIME_PARAMS_LESS_THAN_ONE                          => '至少要有一个时间传入参数',
        Order_Error_Code::NWMS_ORDER_QUERY_RESULT_EMPTY                      => '查询结果为空',
        Order_Error_Code::NWMS_ORDER_RESERVE_ORDER_NOT_EXIST                 => '该预约单不存在，请确认后输入',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_SKU_BUSINESS_FORM_DETAIL_ERROR => 'sku业态详细信息错误',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_SKU_FAILED               => '获取商品信息失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED      => '获取仓储信息失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_CUSTOMER_REGION_ID_ERROR       => '客户区域编号错误',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_CREATE_SHIPMENTORDER_ERROR     => '创建运单失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_NOTIFY_FINISHPICKUP_ERROR      => '通知拣货数量失败',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_EXPECT_ARRIVE_TIME_ERROR       => '预计送达时间错误',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_LATITUDE_ERROR                 => '纬度超出范围',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_LONGITUDE_ERROR                => '经度超出范围',
        Order_Error_Code::NWMS_ORDER_CUSTOMER_LOCATION_SOURCE_ERROR          => '坐标来源标识错误',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_SHELF_ERROR                    => '无人货架信息错误',
        Order_Error_Code::NWMS_STOCKIN_SKU_AMOUNT_DEFECTS_NOT_MATCH          => '商品实际入库数与良品数和非良品数的和不相等',
        Order_Error_Code::NWMS_SKU_LIST_EMPTY                                => '商品列表为空',
        Order_Error_Code::NWMS_STOCKIN_DATA_SOURCE_TYPE_ERROR                => '入库单数据来源错误',
        // 库存调整错误码 开始
        Order_Error_Code::NWMS_ADJUST_STOCKOUT_FAIL                          => '库存调整-出库失败',
        Order_Error_Code::NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR                  => '库存调整-sku效期类型不正确',
        Order_Error_Code::NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR                 => '库存调整-sku id 不存在',
        Order_Error_Code::NWMS_ADJUST_TYPE_ERROR                             => '库存调整-调整类型不正确',
        Order_Error_Code::NWMS_ADJUST_AMOUNT_ERROR                           => '库存调整-调整类型数量不正确',
        Order_Error_Code::NWMS_ADJUST_GET_USER_ERROR                         => '库存调整-获取用户信息（用户名、用户ID）失败',
        Order_Error_Code::NWMS_ADJUST_GET_STOCK_INTO_FAIL                    => '查询商品库存信息失败',
        Order_Error_Code::NWMS_ORDER_ADJUST_GET_SKU_FAILED                   => '获取商品信息失败',
        Order_Error_Code::NWMS_ORDER_ADJUST_GET_CURRENT_SKU_STOCK_FAILED     => '部分商品没有库存信息',
        Order_Error_Code::NWMS_ORDER_ADJUST_SKU_AMOUNT_TOO_MUCH              => '调整SKU个数超过100个',
        Order_Error_Code::NWMS_ORDER_STOCKOUT_ORDER_IS_PRINT                 => '出库单已打印，无法取消',
        Order_Error_Code::NWMS_ORDER_ADJUST_LOCATION_CODE_NOT_EXIST          => '库区编码不存在',
        //销退入库错误码
        Order_Error_Code::INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN => '已作废出库单不允许入库',
        Order_Error_Code::NOT_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN => '未出库出库单不允许入库',
        Order_Error_Code::STOCKIN_ORDER_NOT_EXISTED => '入库单不存在',
        Order_Error_Code::STOCKIN_ORDER_STATUS_INVALID => '入库单已作废,无法入库',
        Order_Error_Code::STOCKIN_ORDER_STATUS_FINISHED => '此订单已入库完成，无需再次入库',
        //冻结单错误码
        Order_Error_Code::NWMS_ORDER_FROZEN_SKU_AMOUNT_TOO_MUCH => '一次最多冻结100个SKU',
        Order_Error_Code::NWMS_FROZEN_ORDER_FROZEN_SKU_STOCK_FAIL => '调用库存模块冻结库存失败',
        Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL => '获取仓库商品冻结数据失败',
        Order_Error_Code::NWMS_FROZEN_ORDER_DETAIL_NOT_EXIST => '冻结单明细获取失败',
        Order_Error_Code::NWMS_FROZEN_ORDER_FROZEN_AMOUNT_ERROR => '冻结单冻结数量不正确',
        Order_Error_Code::NWMS_FROZEN_ORDER_NOT_EXIST => '冻结单不存在',
        Order_Error_Code::NWMS_FROZEN_ORDER_DETAIL_NOT_FOUND => '未找到匹配的冻结单明细',
        Order_Error_Code::NWMS_UNFROZEN_CURRENT_FROZEN_AMOUNT_NOT_NATCH => '当前冻结量不匹配',
        Order_Error_Code::NWMS_UNFROZEN_AMOUNT_OVER_FROZEN_AMOUNT => '解冻数量超过已冻结数量',
        Order_Error_Code::NWMS_UNFROZEN_PARAM_REPEATED => '解冻参数重复',
        Order_Error_Code::NWMS_UNFROZEN_CHECK_VERSION_FAIL => '解冻校验版本失败',
        Order_Error_code::NWMS_FROZEN_ORDER_UNFROZEN_SKU_STOCK_FAIL => '调用库存模块解冻库存失败',
        Order_Error_Code::NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR => '获取仓库商品冻结数据参数有误',
        Order_Error_code::NWMS_UNFROZEN_DETAIL_PARAM_EMPTY => '获取仓库商品冻结数据参数有误',
        Order_Error_code::STOCKOUT_ORDER_PICKUP_ORDER_IS_CREATED=>'当前所选出库单都已经生成拣货单，请勿重复操作',
        Order_Error_code::INVALID_STOCKOUT_ORDER_WAREHOUSE_NOT_CREATE_PICKUP_ORDER=>'当前出库单不在同一个仓库，无法生成拣货单',
        Order_Error_Code::PICKUP_ORDER_NOT_EXISTED => '拣货单不存在',
    ];

}

<?php

/**
 * @name Order_Error_Code
 * @desc Error Code
 * 占位说明：
 * Wm_Error_Code:100000~200000
 * APPXXX_Error_Code 系统异常:200000~300000
 * APPXXX_Error_Code 业务异常:300000~400000
 * @auth wanggang01@iwaimai.baidu.com
 */
class Order_Error_Code extends Wm_Error_Code
{
    /**
     * 正常返回
     * @var integer
     */
    const SUCCESS = 0;

    /**
     * ral异常
     * @var integer
     */
    const RAL_ERROR = 200000;

    /**
     * 参数异常
     * @var integer
     */
    const PARAMS_ERROR = 200001;

    /**
     * =============
     * 业务异常300000
     * =============
     */

    /**
     * 出库单不存在
     * @var integer
     */
    const  STOCKOUT_ORDER_NO_EXISTS = 310005;

    /**
     * 出库单状态不允许修改
     * @var integer
     */
    const  STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE = 310006;

    /**
     * 出库订单更新失败
     * @var integer
     */
    const  STOCKOUT_ORDER_STATUS_UPDATE_FAIL = 310007;

    /**
     * 库存调整失败
     */
    const NWMS_STOCKOUT_ADJUST_SKU_STOCK_FAIL = 310008;

    /**
     * 出库单sku信息不存在
     */
    const NWMS_STOCKOUT_ORDER_SKU_NO_EXISTS = 310009;

    /**
     * 签收数量不存在
     */
    const NWMS_STOCKOUT_ORDER_SIGNUP_SKUS_NOT_EXISTS = 310010;


    /**
     * 拣货失败
     */
    const NWMS_STOCKOUT_ORDER_FINISH_PICKUP_FAIL = 310011;

    /***
     * 拣货数量有误
     */
    const NWMS_STOCKOUT_ORDER_FINISH_PICKUP_AMOUNT_ERROR = 310012;

    /**
     * 预取消状态有误
     */
    const NWMS_STOCKOUT_ORDER_PRE_CANCEL_ERROR = 310014;

    /**
     * 获取彩云系统商品详情信息失败
     * @var integer
     */
    const  STOCKOUT_ORDER_GET_SKUINFO_FAIL = 320001;
    /**
     * nscm采购单号已经被接收
     * @var integer
     */
    const PURCHASE_ORDER_HAS_BEEN_RECEIVED = 330001;

    /**
     * 采购单不存在
     * @var integer
     */
    const PURCHASE_ORDER_NOT_EXIST = 330002;

    /**
     * 订单状态不允许作废
     * @var integer
     */
    const PURCHASE_ORDER_NOT_ALLOW_DESTROY = 330003;

    /**
     * 订单不存在
     * @var int
     */
    const SOURCE_ORDER_ID_NOT_EXIST = 330004;

    /**
     * 同一sku最多只能有两个效期
     * @var int
     */
    const SKU_TOO_MUCH = 330005;

    /**
     * 入库数量不能多于源订单数量
     * @var int
     */
    const STOCKIN_ORDER_AMOUNT_TOO_MUCH = 330006;

    /**
     * sku id 不存在或输入的sku id重复
     * @var int
     */
    const SKU_ID_NOT_EXIST_OR_SKU_ID_REPEAT = 330007;

    /**
     * source order type error
     * @var int
     */
    const SOURCE_ORDER_TYPE_ERROR = 330008;

    /**
     * 映射表不存在
     * @var int
     */
    const TABLE_NOT_EXIST = 330010 ;

    /**
     * 映射orm不存在
     * @var int
     */
    const ORM_NOT_EXIST = 330011 ;

    /**
     * reserve order status not allow stockin
     * @var int
     */
    const RESERVE_ORDER_STATUS_NOT_ALLOW_STOCKIN = 330012;

    /**
     * warehouse not match
     * @var int
     */
    const WAREHOUSE_NOT_MATCH = 330013;

    /**
     * all sku must stock in
     * @var int
     */
    const ALL_SKU_MUST_STOCKIN = 330014;

    /**
     * sku amount cannot empty
     * @var int
     */
    const SKU_AMOUNT_CANNOT_EMPTY = 330015;

    /**
     * total count cannot empty
     * @var int
     */
    const TOTAL_COUNT_CANNOT_EMPTY = 330016;

    /**
     * not ignore warning date
     */
    const NOT_IGNORE_ILLEGAL_DATE = 330017;

    /**
     * 查询时间范围错误
     */
    const QUERY_TIME_SPAN_ERROR = 340000;

    /** 出库单创建失败
     * @var integer
     */
    const NWMS_STOCKOUT_ORDER_CREATE_FAIL = 340001;

    /**
     * 出库单类型失败
     * @var integer
     */
    const NWMS_STOCKOUT_ORDER_TYPE_ERROR = 340002;

    /**
     * 业态订单类型错误
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_TYPE_ERROR = 340003;

    /**
     * 业态订单补货类型错误
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_SUPPLY_TYPE_ERROR = 340004;
    
    /**
     * 业态订单创建sku_id不能为空
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_SKU_ID_EMPTY = 340005;

    /**
     * 创建出库单锁库存失败
     * @var integer
     */
    const NWMS_STOCKOUT_FREEZE_STOCK_FAIL = 340006;

    /**
     * 创建出库单解冻库存失败
     * @var integer
     */
    const NWMS_STOCKOUT_UNFREEZE_STOCK_FAIL = 340007;

    /**
     * 作废出库单失败
     * @var integer
     *
     */
    const NWMS_STOCKOUT_CANCEL_STOCK_FAIL = 340008;

    /**
     * 创建业态订单参数错误
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_PARAMS_ERROR = 340009;

    /**
     * 打印列表ID错误
     * @var integer
     */
    const NWMS_ORDER_PRINT_LIST_ORDER_IDS_ERROR = 340010;

    /**
     * 重复创建订单
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_ORDER_REPEAT_SUBMIT = 340011;

    /**
     * 库存调整-出库失败
     * @var integer
     */
    const NWMS_ADJUST_STOCKOUT_FAIL = 350012;

    /**
     * 库存调整-sku效期类型不识别
     * @var integer
     */
    const NWMS_ADJUST_SKU_EFFECT_TYPE_ERROR = 350013;

    /**
     * 库存调整-sku id 不存在
     * @var integer
     */
    const NWMS_ADJUST_SKU_ID_NOT_EXIST_ERROR = 350014;

    /**
     * 库存调整-调整类型不正确
     * @var integer
     */
    const NWMS_ADJUST_TYPE_ERROR = 350015;

    /**
     * 库存调整-调整类型数量不正确
     * @var integer
     */
    const NWMS_ADJUST_AMOUNT_ERROR = 350016;

    /**
     * 库存调整-获取用户信息（用户名、用户ID）失败
     * @var integer
     */
    const NWMS_ADJUST_GET_USER_ERROR = 350017;

    /**
     * 查询商品库存信息失败
     * @var integer
     */
    const NWMS_ADJUST_GET_STOCK_INTO_FAIL = 350018;

    /**
     * 获取商品信息失败
     * @var integer
     */
    const NWMS_ORDER_ADJUST_GET_SKU_FAILED = 350019;
    
    /**
     * 库存调整-SKU没有库存信息
     * @var integer
     */
    const NWMS_ORDER_ADJUST_GET_CURRENT_SKU_STOCK_FAILED = 350020;


    /**
     * 库存调整-调整SKU个数超过100个
     * @var integer
     */
    const NWMS_ORDER_ADJUST_SKU_AMOUNT_TOO_MUCH = 350021;
    
    /**
     * 获取商品信息失败
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_ORDER_SKU_FAILED = 340012;

    /**
     * 获取仓储信息失败
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_GET_WAREHOUSE_INFO_FAILED = 340013;

    /**
     * 客户区域编号错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_CUSTOMER_REGION_ID_ERROR = 340014;

    /**
     * sku业态详细信息错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_SKU_BUSINESS_FORM_DETAIL_ERROR = 340015;

    /**
     * 业态订单无人货架信息错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_SKU_BUSINESS_SHELF_INFO_ERROR = 340016;

    /**
     * 创建运单失败
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_CREATE_SHIPMENTORDER_ERROR = 340017;

    /**
     * 通知拣货数量错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_NOTIFY_FINISHPICKUP_ERROR = 340018;

    /**
     * 预计送达时间不合法
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_EXPECT_ARRIVE_TIME_ERROR = 340019;

    /**
     * 创建业态订单失败
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR = 340020;

    /**
     * 纬度错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_LATITUDE_ERROR = 340021;

    /**
     * 经度错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_LONGITUDE_ERROR = 340022;

    /**
     * 坐标来源标识错误
     * @var integer
     */
    const NWMS_ORDER_CUSTOMER_LOCATION_SOURCE_ERROR = 340023;

    /**
     * 无人货架信息错误
     * @var integer
     */
    const NWMS_ORDER_STOCKOUT_SHELF_ERROR = 340024;

    /**
     * 商品实际入库数与良品数和非良品数的和不匹配
     * @var integer
     */
    const NWMS_STOCKIN_SKU_AMOUNT_DEFECTS_NOT_MATCH = 340025;

    /**
     * 商品列表为空
     * @var integer
     */
    const NWMS_SKU_LIST_EMPTY = 340026;

    /**
     * 入库单数据来源错误
     */
    const NWMS_STOCKIN_DATA_SOURCE_TYPE_ERROR = 340027;

    /**
     * 至少要有一个时间传入参数
     */
    const TIME_PARAMS_LESS_THAN_ONE = 340028;


    /**
     * 查询返回结果为空
     * @var integer
     */
    const NWMS_ORDER_QUERY_RESULT_EMPTY = 360001;

    /**
     * 该预约单不存在，请确认后输入
     */
    const NWMS_ORDER_RESERVE_ORDER_NOT_EXIST = 360002;

    /**
     * 出库单已打印，无法取消
     */
    const NWMS_ORDER_STOCKOUT_ORDER_IS_PRINT = 310013;



    //------------------------------------------------冻结单------------------------------------------------

    /**
     * 冻结单-冻结SKU个数超过100个
     * @var integer
     */
    const NWMS_ORDER_FROZEN_SKU_AMOUNT_TOO_MUCH = 370001;

    /**
     * 冻结单-调用库存模块冻结库存失败
     * @var integer
     */
    const NWMS_FROZEN_ORDER_FROZEN_SKU_STOCK_FAIL = 370002;

    /**
     * 冻结单-获取仓库商品冻结数据失败
     */
    const NWMS_FROZEN_GET_STOCK_FROZEN_INTO_FAIL = 370003;

    /**
     * 冻结单-冻结单明细获取失败
     * @var integer
     */
    const NWMS_FROZEN_ORDER_DETAIL_NOT_EXIST = 370004;

    /**
     * 冻结单-冻结单信息获取失败
     * @var integer
     */
    const NWMS_FROZEN_ORDER_NOT_EXIST = 370005;

    /**
     * 冻结单-解冻参数重复
     * @var integer
     */
    const NWMS_UNFROZEN_PARAM_REPEATED = 370006;

    /**
     * 冻结单-当前冻结量不匹配
     * @var integer
     */
    const NWMS_UNFROZEN_CURRENT_FROZEN_AMOUNT_NOT_NATCH = 370007;

    /**
     * 冻结单-解冻数量超过已冻结数量
     * @var integer
     */
    const NWMS_UNFROZEN_AMOUNT_OVER_FROZEN_AMOUNT = 370008;

    /**
     * 冻结单冻结数量不正确
     * @var integer
     */
    const NWMS_FROZEN_ORDER_FROZEN_AMOUNT_ERROR = 370009;

    /**
     * 冻结单-未找到匹配的冻结单明细
     * @var integer
     */
    const NWMS_FROZEN_ORDER_DETAIL_NOT_FOUND = 370010;

    /**
     * 冻结单-解冻校验版本失败
     * @var integer
     */
    const NWMS_UNFROZEN_CHECK_VERSION_FAIL = 370011;

    /**
     * 冻结单-调用库存模块解冻库存失败
     * @var integer
     */
    const NWMS_FROZEN_ORDER_UNFROZEN_SKU_STOCK_FAIL = 370012;

    /**
     * 冻结单-获取仓库商品冻结数据参数有误
     */
    const NWMS_FROZEN_GET_STOCK_FROZEN_PARAM_ERROR = 3700013;


    //------------------------------------------------冻结单------------------------------------------------

    /**
     *已作废出库单不允许入库
     */
    const INVALID_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN = 370001;

    /**
     * 未出库出库单不允许入库
     */
    const NOT_STOCKOUT_ORDER_STATUS_NOT_ALLOW_STOCKIN = 370002;

    /**
     * 入库单不存在
     */
    const STOCKIN_ORDER_NOT_EXISTED = 370003;

    /**
     * 入库单已作废,无法入库
     */
    const STOCKIN_ORDER_STATUS_INVALID = 370004;

    /**
     * 此订单已入库完成，无需再次入库
     */
    const STOCKIN_ORDER_STATUS_FINISHED = 370005;
}

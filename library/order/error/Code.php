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

    /**
     * 获取彩云系统商品详情信息失败
     * @var integer
     */
    const  STOCKOUT_ORDER_GET_SKUINFO_FAIL = 320001;

    /**
     * 获取彩云系统商品详情信息失败
     * @var integer
     */
    const  STOCKOUT_ORDER_GET_SKUINFO_ = 320002;

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
     */
    const ORM_NOT_EXIST = 330011 ;

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
     * 创建业态订单失败
     * @var integer
     */
    const NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR = 340008;

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
}

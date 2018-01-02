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
}

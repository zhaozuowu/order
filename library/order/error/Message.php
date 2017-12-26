<?php

/**
 * @name Order_Error_Message
 * @desc Error Code
 * @auth zhaozuowu@iwaimai.baidu.com
 */
class Order_Error_Message extends Wm_Error_Message
{
    /**
     * 正常返回
     */
    const SUCCESS = '';

    /**
     * ral异常
     */
    const RAL_ERROR = '请求第三方异常';

    /**
     * 参数异常
     */
    const PARAMS_ERROR = '参数错误';

    /**
     * 出库单不存在
     * @var integer
     */
    const  STOCKOUT_ORDER_NO_EXISTS = '出库单不存在';

    /**
     * 出库单状态不允许修改
     * @var integer
     */
    const  STOCKOUT_ORDER_STATUS_NOT_ALLOW_UPDATE = '出库单状态不允许修改';

    /**
     * 出库订单更新失败
     * @var integer
     */
    const  STOCKOUT_ORDER_STATUS_UPDATE_FAIL = '出库订单更新失败';


}

<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2017/11/17
 * Time: 18:01
 */

class  Order_Define_Const
{
    /**
     * 未删除
     */
    const NOT_DELETE = 1;
    /**
     * 已删除
     */
    const IS_DELETE = 2;

    /**
     * delete
     * @var array
     */
    const DELETE_MAP = [
        self::NOT_DELETE => '未删除',
        self::IS_DELETE  => '已删除',
    ];

    /**
     * get方法
     */
    const METHOD_GET = 1;

    /**
     * post方法
     */
    const METHOD_POST = 2;

    /**
     * 空数据默认显示为格式
     */
    const DEFAULT_EMPTY_RESULT_STR = '--';

    /**
     * 默认系统操作人
     */
    const DEFAULT_SYSTEM_OPERATION_NAME = '系统';
    const DEFAULT_SYSTEM_OPERATION_ID = '11111111';

    /**
     * 半个小时转化撑秒
     */
    const HALF_AN_HOUR_FORMAT_SECONDS = 1800;

    /**
     * 默认库位编码
     */
    const DEFAULT_LOCATION_CODE = '';
    /**
     * 默认巷道编码
     */
    const DEFAULT_ROADWAY_CODE = '';
    /**
     * 默认库区编码
     */
    const DEFAULT_AREA_CODE = '';

    /**
     * 更新成功
     */
    const UPDATE_SUCCESS = 1;
    /**
     * 更新失败
     */
    const UPDATE_FAILURE = 2;


    const STOCK_INFO_SERVICE = 'StockInfoService';

    const STOCK_CONTROL_SERVICE = 'StockControlService';

    /**
     * Unix时间戳长度 - 30天(24h * 30)，based on second
     * 2592000s === 86400 * 30;
     */
    const UNIX_TIME_SPAN_PER_30_DAYS = 2592000;

    /**
     * Unix时间戳长度 - 7天(24h * 7)，based on second
     * 604800s === 86400 * 7;
     */
    const UNIX_TIME_SPAN_PER_7_DAYS = 604800;

    /**
     * Unix时间戳长度 - 1天(24h)，based on second
     * 86400s === 3600 * 24
     */
    const UNIX_TIME_SPAN_PER_DAY = 86400;

    /**
     * Unix时间戳长度 - 1小时(1h === 60min)，based on second
     * 3600s === 86400 / 24
     */
    const UNIX_TIME_SPAN_PER_HOUR = 3600;

    /**
     * Unix时间戳长度 - 1分钟(1min === 60s)，based on second
     * 60s === 3600 / 60
     */
    const UNIX_TIME_SPAN_PER_MINUTE = 60;

    /**
     * stockin reserve order type
     */
    const APP_NWMS_ORDER_LOG_STOCKIN_RESERVE_TYPE = 7;

    /**
     * stockin stockout log type
     */
    const APP_NWMS_ORDER_LOG_STOCKIN_STOCKOUT_TYPE = 8;

    /**
     * device default
     */
    const DEVICE_DEFAULT = 0;

    /**
     * device RF
     */
    const DEVICE_RF = 1;

    /**
     * device map
     */
    const DEVICE_MAP = [
        self::DEVICE_DEFAULT => '默认设备',
        self::DEVICE_RF => 'RF',
    ];
}

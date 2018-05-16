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
    
}

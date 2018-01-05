<?php
/**
 * @name Order_Define_Cmd
 * @desc Order_Define_Cmd
 * @author jinyu02@iwaimai.baidu.com
 */
class Order_Define_Cmd 
{
    /**
     * 默认topic名称
     * @var string
     */
    const CMD_TOPIC = 'order';

    /**
     * 命令点名称定义
     * @var string
     */
    const CMD_CREATE_STOCKOUT_ORDER = 'cmd_nwms_order_stockout_create';

    const CMD_FINISH_PRICKUP_ORDER  = 'cmd_nwms_order_finish_pickup';
    const CMD_DELETE_STOCKOUT_ORDER  = 'cmd_nwms_stockout_order_delete';

    /**
     * wmq使用的默认配置
     * @var array
     */
    const DEFAULT_WMQ_CONFIG = [
        'Topic' => self::CMD_TOPIC,
        'Key' => '',
        'serviceName' => 'wmqproxy',
    ];
}

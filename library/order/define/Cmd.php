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
     * nwms topic
     * @var string
     */
    const NWMS_ORDER_TOPIC = 'nwmsorder';

    /**
     * nscm sync inbound
     * @var string
     */
    const NSCM_SYNC_INBOUND = 'nscmsyncinbound';

    /**
     * 命令点名称定义
     * @var string
     */
    const CMD_CREATE_STOCKOUT_ORDER = 'cmd_nwms_order_stockout_create';

    const CMD_FINISH_PRICKUP_ORDER  = 'cmd_nwms_order_finish_pickup';
    const CMD_DELETE_STOCKOUT_ORDER  = 'cmd_nwms_stockout_order_delete';


    /**
     * order statistics operate
     * @var string
     */
    const CMD_SYNC_FORM_STATISTICS = 'order_statistics_operate';

    /**
     * reserve order create
     * @var string
     */
    const CMD_CREATE_RESERVE_ORDER = 'reserve_order_create';

    /**
     * cmd sync inbound
     * @var string
     * @deprecated
     */
    const CMD_SYNC_INBOUND = 'sync_inbound';

    /**
     * cmd sync inbound nwms
     * @var string
     */
    const CMD_SYNC_INBOUND_NWMS = 'nscm_purchase_order_sync';

    /**
     * wmq使用的默认配置
     * @var array
     */
    const DEFAULT_WMQ_CONFIG = [
        'Topic' => self::NWMS_ORDER_TOPIC,
        'Key' => '',
        'serviceName' => 'wmqproxy',
    ];
}

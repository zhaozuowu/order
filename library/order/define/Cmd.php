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
     * 创建出库单命令
     * @var string
     */
    const CMD_CREATE_STOCKOUT_ORDER = 'stockout_order_create';

    /**
     * 完成拣货命令
     * @var string
     */
    const CMD_FINISH_PRICKUP_ORDER  = 'stockout_order_finish_pickup';

    /**
     * 拣货完成出库单
     */
    const CMD_FINISH_STOCKOUT_ORDER  = 'pickup_order_finish_stockout';

    /**
     * 作废出库单命令
     * @var string
     */
    const CMD_DELETE_STOCKOUT_ORDER  = 'stockout_order_delete';


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
     * cmd confirm stockin order notify oms
     * @var string
     */
    const CMD_NOTIFY_OMS_CONFIRM_STOCKIN_ORDER = 'notify_oms_confirm_stockin_order';

    /**
     * cmd place order create
     * @var string
     */
    const CMD_PLACE_ORDER_CREATE = 'place_order_create';

    /**
     * wmq使用的默认配置
     * @var array
     */
    const DEFAULT_WMQ_CONFIG = [
        'Topic' => self::NWMS_ORDER_TOPIC,
        'Key' => '',
        'serviceName' => 'wmqproxy',
    ];

    /**
     * important topic reserve order
     * @var string
     */
    const TOPIC_IMPORTANT_CREATE_RESERVE_ORDER = 'nwms_create_reserve_order';

    /**
     * important topic stockout order
     * @var string
     */
    const TOPIC_IMPORTANT_CREATE_STOCKOUT_ORDER = 'nwms_create_stockout_order';

    /**
     * import cmd topic
     * @var array
     */
    const IMPORTANT_CMD_TOPIC = [
        self::CMD_CREATE_RESERVE_ORDER => self::TOPIC_IMPORTANT_CREATE_RESERVE_ORDER,
        self::CMD_CREATE_STOCKOUT_ORDER => self::TOPIC_IMPORTANT_CREATE_STOCKOUT_ORDER,
    ];

    /**
     * @param $strCmd
     * @return string
     */
    public static function getWmqTopic($strCmd) {
        return self::IMPORTANT_CMD_TOPIC[$strCmd] ?? self::NWMS_ORDER_TOPIC;
    }

    /**
     * @return string
     */
    public static function getDefaultWmqTopic() {
        return self::NWMS_ORDER_TOPIC;
    }
}

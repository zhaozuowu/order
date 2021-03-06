<?php
/**
 * @name Order_Define_Wrpc
 * @desc order define wrpc
 * @author jinyu02@iwaimai.baidu.com
 */

class Order_Define_Wrpc
{
    /**
     * tms app id
     * @var string
     */
    const TMS_APP_ID = 'scm.tms_core';

    /**
     * tms namespace
     * @var string
     */
    const TMS_NAMESPACE = 'me.ele.scm.tms.oms.api.omsouter';

    /**
     * tms service name
     * @var string
     */
    const TMS_SERVICE_NAME = 'WmsReferService';
    /**
     * nwms app id
     * @var string
     */
    const NWMS_APP_ID = 'bdwaimai_earthnet.nwms';

    /**
     * nwms_stock_namespace
     * @var string
     */
    const NWMS_STOCK_NAMESPACE = 'stock';

    /**
     * nwms warehouse namespace
     * @var string
     */
    const NWMS_WAREHOUSE_NAMESPACE = 'warehouse';

    /**
     * nwms stock service name
     * @var string
     */
    const NWMS_STOCK_SERVICE_NAME = 'StockOutService';

    /**
     * nwms stock service name
     * @var string
     */
    const STOCK_INFO_SERVICE = 'StockInfoService';

    /**
     * nwms stock control service
     * @var string
     */
    const NWMS_STOCK_CONTROL_SERVICE_NAME = 'StockControlService';

    /**
     * nwms warehouse service name
     * @var string
     */
    const NWMS_WAREHOUSE_SERVICE_NAME = 'WarehouseInfoService';

    /**
     * nwms warehouse service name
     * @var string
     */
    const NWMS_WAREHOUSE_STORAGE_SERVICE_NAME = 'StorageService';

    /**
     * oms app id
     * @var string
     */
    const OMS_APP_ID = 'bdwaimai_earthnet.oms';

    /**
     * oms namespace
     * @var string
     */
    const OMS_NAMESPACE = 'orderui';

    /**
     * oms NWMS service name
     * @var string
     */
    const OMS_NWMS_SERVICE_NAME = 'NwmsService';
    /**
     * oms service name
     * @var string
     */
    const OMS_SERVICE_NAME = 'EventService';
}
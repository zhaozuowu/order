<?php

/**
 * @name Order_Define_StockoutOrder
 * @desc 出库单常量定义
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Order_Define_StockoutOrder
{
    /**
     * 出库单状态列表
     */
    const INIT_STOCKOUT_ORDER_STATUS = 10;//待审核
    const STAY_PICKING_STOCKOUT_ORDER_STATUS = 20;//待拣货
    const STAY_RECEIVED_STOCKOUT_ORDER_STATUS = 25;//待揽收
    const STOCKOUTED_STOCKOUT_ORDER_STATUS = 30;//已出库
    const STOCKOUT_ORDER_AUDIT_FAILED = 40;//审核不通过
    const STOCKOUT_ORDER_DESTROYED = 50;//已作废
    const AUDIT_NOT_THROUGH_STOCKOUT_ORDER_STATUS = 40;//审核不通过
    const INVALID_STOCKOUT_ORDER_STATUS = 50;//已作废


    /**
     * 签收状态
     */
    const  SIGNUP_STATUS_LIST = [
      '1' => '签收',
      '2' => '拒收',
      '3' => '部分签收',
    ];

    /**
     * 出库单状态列表
     * @var array
     */
    const STOCK_OUT_ORDER_STATUS_LIST = [
        '10' => '待审核',
        '20' => '待拣货',
        '25' => '待揽收',
        '30' => '已出库',
        '40' => '审核不通过',
        '50' => '已作废',
    ];


    /**
     * 出库单来源
     * @var array
     */
    const STOCKOUT_ORDER_SOURCE_LIST = [
        '1' => '无人货架',
        '2' => '前置仓',
        '3' => '便利店'
    ];

    const STOCKOUT_ORDER_TYPE_ORDER = 1;
    const STOCKOUT_ORDER_TYPE_RETURN = 2;
    const STOCKOUT_ORDER_TYPE_STOCK = 3;
    /**
     * 出库单类型列表
     * @var array
     */
    const STOCKOUT_ORDER_TYPE_LIST = [
        self::STOCKOUT_ORDER_TYPE_ORDER => '订货出库',
        self::STOCKOUT_ORDER_TYPE_RETURN => '采购退货',
        self::STOCKOUT_ORDER_TYPE_STOCK => '配货出库',
    ];
    /**
     * 出库单打印状态列表
     */
    const STOCKOUT_ORDER_NOT_PRINT = 1;
    const STOCKOUT_ORDER_IS_PRINT = 2;
    /**
     * 出库单打印状态
     * @var array
     */
    const STOCKOUT_PRINT_STATUS = [
        self::STOCKOUT_ORDER_NOT_PRINT => '未打印',
        self::STOCKOUT_ORDER_IS_PRINT => '已打印',
    ];

    const STOCKOUT_ORDER_IS_CANCEL = 1;
    const STOCKOUT_ORDER_NOT_CANCEL = 2;

    const  APP_NWMS_ORDER_APP_ID = 6;//日志app_id
    const  APP_NWMS_ORDER_LOG_TYPE = 6;//业务类型 默认类型
    const  APP_NWMS_ORDER_LOG_TYPE_CREATE = 1;//业务类型 创建出库单
    const  APP_NWMS_ORDER_LOG_TYPE_DELIVERY = 2;//业务类型 完成揽收
    const  APP_NWMS_ORDER_LOG_TYPE_SIGNUP = 3;//业务类型 完成签收
    const  APP_NWMS_ORDER_LOG_TYPE_PICKUP = 4;//业务类型 完成拣货
    const  APP_NWMS_ORDER_LOG_TYPE_CANCLE = 5;//业务类型 作废出库单

    /**
     * 允许入库的状态
     * @var array
     */
    const ALLOW_STOCKIN = [
        self::STOCKOUTED_STOCKOUT_ORDER_STATUS => true,
        self::STOCKOUT_ORDER_DESTROYED => true,
    ];

    /**
     * @desc 操作类型
     */
    const OPERATION_TYPE_INSERT_SUCCESS = 1;
    const OPERATION_TYPE_UPDATE_SUCCESS = 2;
    const OPERATION_TYPE_DELETE_SUCCESS = 3;
    /**
     * 签收状态
     * @var array
     */
    const STOCKOUT_SIGINUP_STATUS_LIST = [
      '1' => '签收',
      '2' => '拒收',
      '3' => '部分签收',
    ];

    /**
     * @desc 出库单的数据来源类型
     */
    const STOCKOUT_DATA_SOURCE_SYSTEM_ORDER = 1;
    const STOCKOUT_DATA_SOURCE_MANUAL_INPUT = 2;
    const STOCKOUT_DATA_SOURCE_OMS = 3;

    /**
     * @desc 出库单的数据来源类型集合
     * @var array
     */
    const STOCKOUT_DATA_SOURCE_TYPES = [
        self::STOCKOUT_DATA_SOURCE_SYSTEM_ORDER => true,
        self::STOCKOUT_DATA_SOURCE_MANUAL_INPUT => true,
    ];

    /**
     * @desc 出库单的数据来源类型文本
     * @var array
     */
    const STOCKOUT_DATA_SOURCE_TYPE_MAP = [
        self::STOCKOUT_DATA_SOURCE_SYSTEM_ORDER => '系统订单',
        self::STOCKOUT_DATA_SOURCE_MANUAL_INPUT => '人工录入',
    ];
    /**
     * 签收状态
     * @var array
     */
    const  STOCKOUT_SIGINUP_ACCEPT_ALL= 1;
    const  STOCKOUT_SIGINUP_REJECT_ALL= 2;
    const  STOCKOUT_SIGINUP_ACCEPT_PART= 3;

    /**
     * 客户列表
     */

    const  CUSTOMER_LIST = [
        'SH03001'=>[
          'customer_id'=>'SH03001',
          'customer_name'=>'南京东路店',
          'customer_contactor'=>'余谨祥',
          'customer_contact'=>'18201701706',
          'customer_address'=>'上海市黄浦区汉口路409号',
          'business_form_order_type'=>'3',
          'business_form_order_type_text'=>'便利店',
           'shelf_info' => self::DEFAULT_SHELF_INFO_LIST,
           'executor'   =>'余谨祥',
           'executor_contact'=>'18201701706',
           'customer_location'=>'121.489496,31.240967',
           'customer_region_id'=>'5251',
           'customer_city_id'=>'1',
           'customer_city_name'=>'上海',
           'customer_region_name'=>'黄浦区',
           'customer_location_source'=>'2',
        ],
        'NJ03001'=>[
          'customer_id'=>'NJ03001',
          'customer_name'=>'珠江路店',
          'customer_contactor'=>'施高峰',
          'customer_contact'=>'18052520777',
          'customer_address'=>'江苏省南京市玄武区珠江路291号',
            'business_form_order_type'=>'3',
            'business_form_order_type_text'=>'便利店',
            'shelf_info' => self::DEFAULT_SHELF_INFO_LIST,
            'executor'   =>'施高峰',
            'executor_contact'=>'18052520777',
            'customer_location'=>'118.799224,32.055611',
            'customer_region_id'=>'5267',
            'customer_city_id'=>'6',
            'customer_city_name'=>'南京',
            'customer_region_name'=>'玄武区',
            'customer_location_source'=>'2',
        ],
        'CZ03001'=>[
          'customer_id'=>'CZ03001',
          'customer_name'=>'南大街店',
          'customer_contactor'=>'施乃康',
          'customer_contact'=>'15861161658',
          'customer_address'=>'江苏省常州市钟楼区广化街190-198号',
            'business_form_order_type'=>'3',
            'business_form_order_type_text'=>'便利店',
            'shelf_info' => self::DEFAULT_SHELF_INFO_LIST,
            'executor'   =>'施乃康',
            'executor_contact'=>'15861161658',
            'customer_location'=>'119.958925,31.778679',
            'customer_region_id'=>'5290',
            'customer_city_id'=>'59',
            'customer_city_name'=>'常州',
            'customer_region_name'=>'钟楼区',
            'customer_location_source'=>'2',
        ],
        'CZ03002'=>[
          'customer_id'=>'CZ03002',
          'customer_name'=>'新北店',
          'customer_contactor'=>'谈欢欢',
          'customer_contact'=>'13861010119',
          'customer_address'=>'江苏省常州市新北区三井街道竹山路福地聚龙苑36号',
            'business_form_order_type'=>'3',
            'business_form_order_type_text'=>'便利店',
            'shelf_info' => self::DEFAULT_SHELF_INFO_LIST,
            'executor'   =>'谈欢欢',
            'executor_contact'=>'13861010119',
            'customer_location'=>'119.974091,31.821031',
            'customer_region_id'=>'5292',
            'customer_city_id'=>'59',
            'customer_city_name'=>'常州',
            'customer_region_name'=>'新北区',
            'customer_location_source'=>'2',
        ],
    ];

    /***
     * upc_ids数量
     */
    const UPC_IDS_NUM_TWO = 2;

    /**
     * 无人货架信息
     */
    const DEFAULT_SHELF_INFO_LIST = [
      'supply_type'=>Order_Define_BusinessFormOrder::ORDER_SUPPLY_TYPE_ORDER,
      'devices'=> array(),
    ];

    /**
<<<<<<< HEAD
     * 预取消标识
     */
    const STOCKOUT_ORDER_DEFAULT_PRE_CANCEL = 0;
    const STOCKOUT_ORDER_IS_PRE_CANCEL = 1;
    const STOCKOUT_ORDER_NOT_PRE_CANCEL = 2;

    /**
     * @desc 预取消标识字段映射
     * @var array
     */
    const STOCKOUT_ORDER_PRE_CANCEL_MAP = [
        self::STOCKOUT_ORDER_IS_PRE_CANCEL => '预取消',
        self::STOCKOUT_ORDER_NOT_PRE_CANCEL => '非预取消',
    ];

    /**
     * @desc 预取消标识字段映射
     * @var array
     */
    const STOCKOUT_ORDER_CANCEL_TYPE_MAP= [
        self::STOCKOUT_ORDER_CANCEL_TYPE_SYS => '系统取消',
        self::STOCKOUT_ORDER_NOT_PRE_MANUAL => '人工取消',
    ];
    /**
     * 预取消来源
     */
    const STOCKOUT_ORDER_CANCEL_TYPE_DEFAULT = 0;
    const STOCKOUT_ORDER_CANCEL_TYPE_SYS = 1;
    const STOCKOUT_ORDER_NOT_PRE_MANUAL = 2;

    /**
     * stockout_order_stock_status history
     * @var int
     */
    const STOCK_STATUS_HISTORY = 0;

    /**
     * stockout_order_stock_status unsure
     * @var int
     */
    const STOCK_STATUS_UNSURE = 1;

    /**
     * stockout_order_stock_status sure
     * @var int
     */
    const STOCK_STATUS_SURE = 2;

    /**
     * sku event is back
     */
    const SKUS_EVENTS_IS_BACK_MAP = [
        '1' => false,
        '2'=>true,
        '3'=>true,
    ];
}
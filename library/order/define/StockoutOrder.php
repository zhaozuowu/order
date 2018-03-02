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
    const STOCKOUT_ORDER_DESTORYED = 50;//已作废
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
    /**
     * 包装单位
     *@var array
     */
    const  UPC_UNIT = [
        1 => "箱",
        2 => "袋",
        3 => "包",
        4 => "瓶",
        5 => "盒",
        6 => "罐",
        7 => "条",
        8 => "件",
        9 => "个",
        10 => "桶",
        11 => "杯",
        12 => "根",

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
    const  APP_NWMS_ORDER_LOG_TYPE = 6;//业务类型

    /**
     * 允许入库的状态
     * @var array
     */
    const ALLOW_STOCKIN = [
        self::STOCKOUTED_STOCKOUT_ORDER_STATUS => true,
        self::STOCKOUT_ORDER_DESTORYED => true,
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
        '27272727272778'=>[
          'customer_id'=>'27272727272778',
          'customer_name'=>'测试1',
          'customer_contactor'=>'测试1',
          'customer_contact'=>'13146104867',
          'customer_address'=>'上地四街',
        ],
        '27272727272779'=>[
          'customer_id'=>'27272727272779',
          'customer_name'=>'测试2',
          'customer_contactor'=>'测试2',
          'customer_contact'=>'13146104868',
          'customer_address'=>'上地四街',
        ],
        '27272727272780'=>[
          'customer_id'=>'27272727272780',
          'customer_name'=>'测试3',
          'customer_contactor'=>'测试3',
          'customer_contact'=>'13146104868',
          'customer_address'=>'上地四街',
        ],
        '27272727272781'=>[
          'customer_id'=>'27272727272781',
          'customer_name'=>'测试4',
          'customer_contactor'=>'测试4',
          'customer_contact'=>'13146104868',
          'customer_address'=>'上地四街',
        ],
    ];

    /***
     * upc_ids数量
     */
    const UPC_IDS_NUM_TWO = 2;
}
<?php
/**
 * @name Action_GetPickupOrderList
 * @desc get pick up order list
 * @author wanggang01@iwaimai.baidu.com
 */

class Action_GetPickupOrderList extends Order_Base_Action
{
    /**
     * 判断是否有权限
     *
     * @var boolean
     */
    protected $boolCheckAuth = false;

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required',
        'warehouse_id' => 'str',
        'pickup_order_id' => 'str',
        'stockout_order_id' => 'str',
        'pickup_order_is_print' => 'int',
        'create_start_time' => 'int|required',
        'create_end_time' => 'int|required',
        'update_start_time' => 'int',
        'update_end_time' => 'int',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderList();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $ret = [
            'total' => 0,
            'orders' => [],
        ];
        if (!empty($data['total'])) {
            $ret['total'] = $data['total'];
        }
        foreach ((array)$data['rows'] as $item) {
            $ret['orders'][] = [
                'pickup_order_id' => $item['pickup_order_id'],
                'pickup_order_type' => $item['pickup_order_type'],
                'pickup_order_is_print' => $item['pickup_order_is_print'],
                'pickup_order_status' => $item['pickup_order_status'],
                'stockout_order_amount' => $item['stockout_order_amount'],
                'sku_kind_amount' => $item['sku_kind_amount'],
                'sku_pickup_amount' => $item['sku_pickup_amount'],
                'sku_distribute_amount' => $item['sku_distribute_amount'],
                'creator' => $item['creator'],
                'create_time' => $item['create_time'],
                'update_operator' => $item['update_operator'],
                'update_time' => $item['update_time'],
            ];
        }
        return $ret;
    }
}
